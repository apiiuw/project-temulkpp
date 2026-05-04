<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AturUlangJadwalController extends Controller
{
    public function index(Request $request)
    {
        $availableTimes = $this->getAvailableTimes();
        $searchedCode = strtoupper(trim((string) $request->query('kode_reservasi', '')));
        $reservation = null;

        if ($searchedCode !== '' && Schema::hasTable('reservations')) {
            $reservation = Reservation::where('kode_reservasi', $searchedCode)->first();
        }

        $occupiedSlots = $this->getOccupiedSlots($reservation?->id);

        $currentDate = '';
        $currentTime = '';

        if ($reservation) {
            $currentDate = Carbon::parse($reservation->tanggal_jam)->format('Y-m-d');
            $currentTime = Carbon::parse($reservation->tanggal_jam)->format('H:i');
        }

        return view('roles.users.aturUlangJadwal.index', [
            'title' => 'Atur Ulang Jadwal',
            'availableTimes' => $availableTimes,
            'occupiedSlots' => $occupiedSlots,
            'reservation' => $reservation,
            'searchedCode' => $searchedCode,
            'currentDate' => $currentDate,
            'currentTime' => $currentTime,
        ]);
    }

    public function update(Request $request, string $kodeReservasi)
    {
        $reservation = Reservation::where('kode_reservasi', $kodeReservasi)->firstOrFail();

        $validatedData = $request->validate([
            'tanggal_jam' => 'required|date',
        ]);

        $newDateTime = Carbon::parse($validatedData['tanggal_jam'])->seconds(0);
        $now = Carbon::now()->seconds(0);

        if ($newDateTime->lessThanOrEqualTo($now)) {
            return back()->withErrors([
                'tanggal_jam' => 'Tanggal dan jam penjadwalan ulang hanya bisa dipilih mulai setelah waktu saat ini.',
            ])->withInput();
        }

        if (! $this->isAllowedReservationDay($newDateTime)) {
            return back()->withErrors([
                'tanggal_jam' => 'Penjadwalan ulang hanya tersedia pada hari Senin sampai Kamis.',
            ])->withInput();
        }

        if (! in_array($newDateTime->format('H:i'), $this->getAvailableTimes(), true)) {
            return back()->withErrors([
                'tanggal_jam' => 'Jam penjadwalan ulang hanya tersedia setiap 40 menit mulai 08:00 sampai 16:00 WIB, dengan jeda istirahat 12:00 sampai 13:00 WIB.',
            ])->withInput();
        }

        if (Reservation::where('tanggal_jam', $newDateTime->format('Y-m-d H:i:s'))
            ->where('id', '!=', $reservation->id)
            ->count() >= 7) {
            return back()->withErrors([
                'tanggal_jam' => 'Jadwal baru yang dipilih sudah penuh (maksimal 7 reservasi per slot). Silakan pilih slot lain.',
            ])->withInput();
        }

        $newReservation = DB::transaction(function () use ($reservation, $newDateTime) {
            $payload = [
                'nama_lengkap' => $reservation->nama_lengkap,
                'jabatan' => $reservation->jabatan,
                'asal_pt' => $reservation->asal_pt,
                'jenis_layanan' => $reservation->jenis_layanan,
                'detail_keperluan' => $reservation->detail_keperluan,
                'lampiran' => $reservation->lampiran,
                'tanggal_jam' => $newDateTime->format('Y-m-d H:i:s'),
                'kode_reservasi' => $this->generateReservationCode(),
            ];

            $newReservation = Reservation::create($payload);

            $reservation->delete();

            return $newReservation;
        });

        return redirect()->route('atur-ulang-jadwal', [
            'kode_reservasi' => $newReservation->kode_reservasi,
        ])->with('reschedule_success', 'Reservasi berhasil dijadwalkan ulang dengan kode baru.');
    }

    public function destroy(string $kodeReservasi)
    {
        $reservation = Reservation::where('kode_reservasi', $kodeReservasi)->firstOrFail();
        $reservation->delete();

        return redirect()->route('atur-ulang-jadwal')->with('cancel_success', 'Reservasi berhasil dibatalkan.');
    }

    private function getOccupiedSlots(?int $excludeReservationId = null)
    {
        if (! Schema::hasTable('reservations')) {
            return collect();
        }

        $query = Reservation::query()->select('id', 'tanggal_jam');

        if ($excludeReservationId !== null) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->get()
            ->groupBy(fn (Reservation $reservation) => Carbon::parse($reservation->tanggal_jam)->format('Y-m-d'))
            ->map(fn ($reservations) => $reservations
                ->groupBy(fn (Reservation $reservation) => Carbon::parse($reservation->tanggal_jam)->format('H:i'))
                ->filter(fn ($group) => $group->count() >= 7)
                ->keys()
                ->all()
            );
    }

    private function generateReservationCode(): string
    {
        $kodeReservasi = 'RES-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        while (Reservation::where('kode_reservasi', $kodeReservasi)->exists()) {
            $kodeReservasi = 'RES-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        }

        return $kodeReservasi;
    }

    private function isAllowedReservationDay(Carbon $dateTime): bool
    {
        return $dateTime->dayOfWeekIso >= 1 && $dateTime->dayOfWeekIso <= 4;
    }

    private function getAvailableTimes(): array
    {
        $times = [];
        $morningCurrent = Carbon::createFromTime(8, 0, 0);
        $morningEnd = Carbon::createFromTime(11, 20, 0);
        $afternoonCurrent = Carbon::createFromTime(13, 0, 0);
        $afternoonEnd = Carbon::createFromTime(15, 40, 0);

        while ($morningCurrent->lte($morningEnd)) {
            $times[] = $morningCurrent->format('H:i');
            $morningCurrent->addMinutes(40);
        }

        while ($afternoonCurrent->lte($afternoonEnd)) {
            $times[] = $afternoonCurrent->format('H:i');
            $afternoonCurrent->addMinutes(40);
        }

        return $times;
    }
}
