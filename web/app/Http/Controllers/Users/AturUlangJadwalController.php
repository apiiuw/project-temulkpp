<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationSetting;
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
                'tanggal_jam' => 'Jam penjadwalan ulang tidak tersedia pada slot yang dipilih.',
            ])->withInput();
        }

        if (Reservation::where('tanggal_jam', $newDateTime->format('Y-m-d H:i:s'))
            ->where('id', '!=', $reservation->id)
            ->count() >= $this->maxReservationsPerSlot()) {
            return back()->withErrors([
                'tanggal_jam' => 'Jadwal baru yang dipilih sudah penuh. Silakan pilih slot lain.',
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
                'tahun' => $newDateTime->year,
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
                ->filter(fn ($group) => $group->count() >= $this->maxReservationsPerSlot())
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
        $days = json_decode((string) (ReservationSetting::where('key', 'available_days')->value('value') ?? '[1,2,3,4]'), true);

        return in_array($dateTime->dayOfWeekIso, $days, true);
    }

    private function getAvailableTimes(): array
    {
        $settings = ReservationSetting::whereIn('key', [
            'morning_start', 'morning_end', 'afternoon_start', 'afternoon_end', 'consultation_duration_minutes',
        ])->pluck('value', 'key');
        $times = [];
        $duration = (int) ($settings['consultation_duration_minutes'] ?? 40);
        $morningCurrent = Carbon::createFromFormat('H:i', $settings['morning_start'] ?? '08:00');
        $morningEnd = Carbon::createFromFormat('H:i', $settings['morning_end'] ?? '11:20');
        $afternoonCurrent = Carbon::createFromFormat('H:i', $settings['afternoon_start'] ?? '13:00');
        $afternoonEnd = Carbon::createFromFormat('H:i', $settings['afternoon_end'] ?? '15:40');

        while ($morningCurrent->lte($morningEnd)) {
            $times[] = $morningCurrent->format('H:i');
            $morningCurrent->addMinutes($duration);
        }

        while ($afternoonCurrent->lte($afternoonEnd)) {
            $times[] = $afternoonCurrent->format('H:i');
            $afternoonCurrent->addMinutes($duration);
        }

        return $times;
    }

    private function maxReservationsPerSlot(): int
    {
        return (int) (ReservationSetting::where('key', 'max_reservations_per_slot')->value('value') ?? 7);
    }
}
