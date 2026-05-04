<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationCheckInController extends Controller
{
    private const LATE_CONFIRMATION_GRACE_MINUTES = 15;

    public function index(Request $request)
    {
        $searchedCode = strtoupper(trim((string) $request->query('kode_reservasi', '')));
        $reservation = null;
        $reservationTiming = null;

        if ($searchedCode !== '') {
            $reservation = Reservation::where('kode_reservasi', $searchedCode)->first();

            if ($reservation) {
                $reservation = $this->refreshReservationStatus($reservation);
                $reservationTiming = $this->buildReservationTiming($reservation);
            }
        }

        return view('roles.frontDesk.index', [
            'title' => 'Front Desk',
            'searchedCode' => $searchedCode,
            'reservation' => $reservation,
            'reservationTiming' => $reservationTiming,
            'lateConfirmationGraceMinutes' => self::LATE_CONFIRMATION_GRACE_MINUTES,
        ]);
    }

    public function confirm(string $kodeReservasi)
    {
        $reservation = Reservation::where('kode_reservasi', $kodeReservasi)->firstOrFail();
        $reservation = $this->refreshReservationStatus($reservation);
        $now = Carbon::now();
        $reservationTime = $this->resolveReservationTime($reservation);
        $graceDeadline = $this->resolveGraceDeadline($reservationTime);

        if ($reservation->status === 'expired_front_desk') {
            return redirect()->route('front-desk', [
                'kode_reservasi' => $reservation->kode_reservasi,
            ])->with('check_in_error', 'Reservasi ini sudah melewati batas keterlambatan 15 menit sehingga tidak dapat dikonfirmasi di front desk.');
        }

        if ($now->toDateString() !== $reservationTime->toDateString()) {
            return redirect()->route('front-desk', [
                'kode_reservasi' => $reservation->kode_reservasi,
            ])->with('check_in_error', 'Konfirmasi kedatangan hanya bisa dilakukan pada hari yang sama dengan tanggal reservasi.');
        }

        if ($now->greaterThan($graceDeadline)) {
            $reservation->update([
                'status' => 'expired_front_desk',
            ]);

            return redirect()->route('front-desk', [
                'kode_reservasi' => $reservation->kode_reservasi,
            ])->with('check_in_error', 'Reservasi ini sudah melewati batas keterlambatan 15 menit sehingga tidak dapat dikonfirmasi di front desk.');
        }

        if ($reservation->status !== 'checked_in_front_desk') {
            $assignedAgentId = $reservation->agent_id;

            if (! $assignedAgentId) {
                $assignedAgentId = $this->resolveFallbackAgentId($reservationTime);
            }

            $reservation->update([
                'status' => 'checked_in_front_desk',
                'checked_in_at' => Carbon::now(),
                'agent_id' => $assignedAgentId,
            ]);
        }

        $agentInfo = $reservation->agent ? ' Tamu diarahkan ke ' . $reservation->agent->name . '.' : '';

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Kedatangan tamu berhasil dikonfirmasi.' . $agentInfo,
                'kode_reservasi' => $reservation->kode_reservasi,
            ]);
        }

        return redirect()->route('front-desk', [
            'kode_reservasi' => $reservation->kode_reservasi,
        ])->with('check_in_success', 'Kedatangan tamu berhasil dikonfirmasi.' . $agentInfo);
    }

    private function refreshReservationStatus(Reservation $reservation): Reservation
    {
        $reservationTime = $this->resolveReservationTime($reservation);
        $now = Carbon::now();
        $graceDeadline = $this->resolveGraceDeadline($reservationTime);

        if ($reservation->status === 'pending' && $now->greaterThan($graceDeadline)) {
            $reservation->update([
                'status' => 'expired_front_desk',
            ]);

            $reservation->refresh();
        }

        // Self-healing for premature expiration due clock mismatch/cache stale state.
        if (
            $reservation->status === 'expired_front_desk'
            && $now->toDateString() === $reservationTime->toDateString()
            && $now->lessThanOrEqualTo($graceDeadline)
        ) {
            $reservation->update([
                'status' => 'pending',
            ]);

            $reservation->refresh();
        }

        return $reservation;
    }

    private function buildReservationTiming(Reservation $reservation): array
    {
        $reservationTime = $this->resolveReservationTime($reservation);
        $now = Carbon::now();
        $graceDeadline = $this->resolveGraceDeadline($reservationTime);
        $remainingSeconds = max(0, $now->diffInSeconds($graceDeadline, false));

        return [
            'is_late' => $now->greaterThan($reservationTime) && $remainingSeconds > 0,
            'remaining_seconds' => $remainingSeconds,
            'grace_deadline_iso' => $graceDeadline->toIso8601String(),
        ];
    }

    private function resolveGraceDeadline(Carbon $reservationTime): Carbon
    {
        return $reservationTime->copy()->addMinutes(self::LATE_CONFIRMATION_GRACE_MINUTES);
    }

    private function resolveReservationTime(Reservation $reservation): Carbon
    {
        $rawValue = $reservation->getRawOriginal('tanggal_jam');

        if ($rawValue instanceof Carbon) {
            $rawValue = $rawValue->format('Y-m-d H:i:s');
        }

        $normalizedValue = substr(str_replace('T', ' ', (string) ($rawValue ?: $reservation->tanggal_jam)), 0, 19);

        return Carbon::createFromFormat('Y-m-d H:i:s', $normalizedValue, config('app.timezone'));
    }

    private function resolveFallbackAgentId(Carbon $reservationTime): ?int
    {
        $candidateAgents = Agent::query()
            ->withCount([
                'reservations as same_day_reservations_count' => fn ($query) => $query
                    ->whereDate('tanggal_jam', $reservationTime->toDateString()),
                'reservations as active_reservations_count' => fn ($query) => $query
                    ->whereIn('status', ['pending', 'checked_in_front_desk', 'in_progress']),
            ])
            ->get();

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
        $seed = crc32($reservationTime->format('Y-m-d H:i') . '|' . $poolSignature);
        $index = abs((int) $seed) % $fairPool->count();

        return $fairPool->get($index)?->id;
    }
}
