<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Reservation;
use App\Models\ServiceType;
use App\Models\ReservationSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class ReservasiController extends Controller
{
    public function index()
    {
        $availableTimes = $this->getAvailableTimes();
        $occupiedSlots = collect();

        if (Schema::hasTable('reservations')) {
            $occupiedSlots = Reservation::query()
                ->select('tanggal_jam')
                ->get()
                ->groupBy(fn (Reservation $reservation) => Carbon::parse($reservation->tanggal_jam)->format('Y-m-d'))
                ->map(fn ($reservations) => $reservations
                    ->groupBy(fn (Reservation $reservation) => Carbon::parse($reservation->tanggal_jam)->format('H:i'))
                    ->filter(fn ($group) => $group->count() >= 7)
                    ->keys()
                    ->all()
                );
        }

        $serviceTypes = ServiceType::where('is_active', true)->get();
        $allowedDays = json_decode(ReservationSetting::where('key', 'available_days')->value('value') ?? '[1,2,3,4]', true);

        return view('roles.users.reservasi.index', [
            'title' => 'Reservasi',
            'availableTimes' => $availableTimes,
            'occupiedSlots' => $occupiedSlots,
            'serviceTypes' => $serviceTypes,
            'allowedDays' => $allowedDays,
        ]);
    }

    public function store(Request $request)
    {
        $allowedServiceTypes = ServiceType::where('is_active', true)->pluck('name')->toArray();

        $validatedData = $request->validate([
            'nama_lengkap' => 'required|string|max:50',
            'jabatan' => 'required|string|max:100',
            'asal_pt' => 'required|string|max:100',
            'jenis_layanan' => 'required|in:' . implode(',', $allowedServiceTypes),
            'detail_keperluan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'tanggal_jam' => 'required|date',
        ]);

        $reservationDateTime = Carbon::parse($validatedData['tanggal_jam'])->seconds(0);
        $now = Carbon::now()->seconds(0);

        if ($reservationDateTime->lessThanOrEqualTo($now)) {
            return back()->withErrors([
                'tanggal_jam' => 'Tanggal dan jam reservasi hanya bisa dipilih mulai setelah waktu saat ini.',
            ])->withInput();
        }

        if (! $this->isAllowedReservationDay($reservationDateTime)) {
            return back()->withErrors([
                'tanggal_jam' => 'Reservasi hanya tersedia pada hari Senin sampai Kamis.',
            ])->withInput();
        }

        if (! in_array($reservationDateTime->format('H:i'), $this->getAvailableTimes(), true)) {
            return back()->withErrors([
                'tanggal_jam' => 'Jam reservasi hanya tersedia setiap 40 menit mulai 08:00 sampai 16:00 WIB, dengan jeda istirahat 12:00 sampai 13:00 WIB.',
            ])->withInput();
        }

        $maxPerSlot = (int) (ReservationSetting::where('key', 'max_reservations_per_slot')->value('value') ?? 7);

        if (Reservation::where('tanggal_jam', $reservationDateTime->format('Y-m-d H:i:s'))->count() >= $maxPerSlot) {
            return back()->withErrors([
                'tanggal_jam' => "Jadwal yang dipilih sudah penuh (maksimal {$maxPerSlot} reservasi per slot). Silakan pilih slot lain.",
            ])->withInput();
        }

        $validatedData['tanggal_jam'] = $reservationDateTime->format('Y-m-d H:i:s');

        $kode_reservasi = 'RES-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        while (Reservation::where('kode_reservasi', $kode_reservasi)->exists()) {
            $kode_reservasi = 'RES-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        }

        $validatedData['kode_reservasi'] = $kode_reservasi;

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = 'LAMPIRAN-' . $kode_reservasi . '.' . $extension;
            $path = $file->storeAs('lampirans', $filename, 'public');
            $validatedData['lampiran'] = $path;
        }

        $validatedData['agent_id'] = $this->resolveAssignedAgentId($reservationDateTime);

        Reservation::create($validatedData);

        return redirect()->back()->with([
            'success' => 'Reservasi berhasil dibuat!',
            'kode_reservasi' => $kode_reservasi,
        ]);
    }

    public function download(string $kodeReservasi)
    {
        $reservation = Reservation::where('kode_reservasi', $kodeReservasi)->firstOrFail();
        $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->generate($reservation->kode_reservasi);

        $pdf = Pdf::loadView('roles.users.reservasi.pdf', [
            'reservation' => $reservation,
            'formattedSchedule' => Carbon::parse($reservation->tanggal_jam)->translatedFormat('d F Y, H:i'),
            'qrCodeDataUri' => 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg),
        ])->setPaper('a4');

        return $pdf->download('reservasi-' . $reservation->kode_reservasi . '.pdf');
    }

    private function isAllowedReservationDay(Carbon $dateTime): bool
    {
        $allowedDays = json_decode(ReservationSetting::where('key', 'available_days')->value('value') ?? '[1,2,3,4]', true);
        return in_array($dateTime->dayOfWeekIso, $allowedDays);
    }

    private function getAvailableTimes(): array
    {
        $settings = ReservationSetting::whereIn('key', [
            'morning_start', 'morning_end', 'afternoon_start', 'afternoon_end', 'consultation_duration_minutes'
        ])->pluck('value', 'key');

        $morningStart = $settings['morning_start'] ?? '08:00';
        $morningEnd = $settings['morning_end'] ?? '11:20';
        $afternoonStart = $settings['afternoon_start'] ?? '13:00';
        $afternoonEnd = $settings['afternoon_end'] ?? '15:40';
        $duration = (int) ($settings['consultation_duration_minutes'] ?? 40);

        $times = [];
        $morningCurrent = Carbon::createFromFormat('H:i', $morningStart);
        $morningLast = Carbon::createFromFormat('H:i', $morningEnd);
        $afternoonCurrent = Carbon::createFromFormat('H:i', $afternoonStart);
        $afternoonLast = Carbon::createFromFormat('H:i', $afternoonEnd);

        while ($morningCurrent->lte($morningLast)) {
            $times[] = $morningCurrent->format('H:i');
            $morningCurrent->addMinutes($duration);
        }

        while ($afternoonCurrent->lte($afternoonLast)) {
            $times[] = $afternoonCurrent->format('H:i');
            $afternoonCurrent->addMinutes($duration);
        }

        return $times;
    }

    private function resolveAssignedAgentId(Carbon $reservationDateTime): ?int
    {
        $assignedAgentIdsInSlot = Reservation::query()
            ->where('tanggal_jam', $reservationDateTime->format('Y-m-d H:i:s'))
            ->whereNotNull('agent_id')
            ->pluck('agent_id');

        $candidateAgents = Agent::query()
            ->when(
                $assignedAgentIdsInSlot->isNotEmpty(),
                fn ($query) => $query->whereNotIn('id', $assignedAgentIdsInSlot)
            )
            ->withCount([
                'reservations as same_day_reservations_count' => fn ($query) => $query
                    ->whereDate('tanggal_jam', $reservationDateTime->toDateString()),
                'reservations as active_reservations_count' => fn ($query) => $query
                    ->whereIn('status', ['pending', 'checked_in_front_desk', 'in_progress']),
            ])
            ->get();

        if ($candidateAgents->isEmpty()) {
            $candidateAgents = Agent::query()
                ->withCount([
                    'reservations as same_day_reservations_count' => fn ($query) => $query
                        ->whereDate('tanggal_jam', $reservationDateTime->toDateString()),
                    'reservations as active_reservations_count' => fn ($query) => $query
                        ->whereIn('status', ['pending', 'checked_in_front_desk', 'in_progress']),
                ])
                ->get();
        }

        return $this->pickFairAgentId($candidateAgents, $reservationDateTime);
    }

    private function pickFairAgentId(Collection $candidateAgents, Carbon $reservationDateTime): ?int
    {
        if ($candidateAgents->isEmpty()) {
            return null;
        }

        $minSameDayCount = $candidateAgents->min('same_day_reservations_count');
        $sameDayPool = $candidateAgents
            ->where('same_day_reservations_count', $minSameDayCount)
            ->values();

        $minActiveCount = $sameDayPool->min('active_reservations_count');
        $fairPool = $sameDayPool
            ->where('active_reservations_count', $minActiveCount)
            ->sortBy('id')
            ->values();

        if ($fairPool->count() === 1) {
            return $fairPool->first()->id;
        }

        $poolSignature = $fairPool->pluck('id')->implode('-');
        $seed = crc32($reservationDateTime->format('Y-m-d H:i') . '|' . $poolSignature);
        $index = abs((int) $seed) % $fairPool->count();

        return $fairPool->get($index)?->id;
    }
}
