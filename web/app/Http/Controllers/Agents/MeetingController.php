<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    private const DEFAULT_SESSION_DURATION_SECONDS = 2400;

    public function start(string $kodeReservasi)
    {
        $reservation = $this->resolveAgentReservation($kodeReservasi);

        if ($reservation->status === 'checked_in_front_desk') {
            $reservation->update([
                'status' => 'in_progress',
                'waktu_mulai_tatap_muka' => Carbon::now(),
            ]);
        }

        return redirect()->route('agent.tatap-muka', $kodeReservasi);
    }

    public function show(string $kodeReservasi)
    {
        $reservation = $this->resolveAgentReservation($kodeReservasi);

        if (! $reservation->waktu_mulai_tatap_muka) {
            $reservation->update([
                'waktu_mulai_tatap_muka' => Carbon::now(),
                'status' => 'in_progress',
            ]);
            $reservation->refresh();
        }

        $startTime = Carbon::parse($reservation->waktu_mulai_tatap_muka);
        $sessionDurationSeconds = $this->resolveSessionDurationSeconds($reservation);
        $endTime = $startTime->copy()->addSeconds($sessionDurationSeconds);
        $sessionDurationLabel = gmdate('i:s', $sessionDurationSeconds);

        $tamuLampiranUrl = $reservation->lampiran ? asset('storage/' . ltrim($reservation->lampiran, '/')) : null;

        return view('roles.agents.meeting.show', [
            'title' => 'Tatap Muka - ' . $reservation->nama_lengkap,
            'reservation' => $reservation,
            'tamuLampiranUrl' => $tamuLampiranUrl,
            'startTime' => $startTime->toIso8601String(),
            'endTime' => $endTime->toIso8601String(),
            'sessionDurationLabel' => $sessionDurationLabel,
        ]);
    }

    public function end(Request $request, string $kodeReservasi)
    {
        $reservation = $this->resolveAgentReservation($kodeReservasi);

        $request->validate([
            'catatan' => 'required|string|min:5',
            'file_catatan' => 'nullable|file|max:5120',
        ]);

        $filePath = $reservation->catatan_tatap_muka;
        if ($request->hasFile('file_catatan')) {
            $file = $request->file('file_catatan');
            $extension = $file->getClientOriginalExtension();
            $filename = 'CATATAN-' . $kodeReservasi . '.' . $extension;
            $filePath = $file->storeAs('catatan', $filename, 'public');
        }

        $reservation->update([
            'status' => 'completed',
            'waktu_selesai_tatap_muka' => Carbon::now(),
            'catatan_tatap_muka' => json_encode([
                'teks' => $request->catatan,
                'file' => $filePath,
            ]),
        ]);

        return redirect()->route('agent.dashboard')->with('success', 'Sesi tatap muka berhasil diselesaikan.');
    }

    private function resolveAgentReservation(string $kodeReservasi): Reservation
    {
        return Reservation::query()
            ->where('kode_reservasi', $kodeReservasi)
            ->where('agent_id', Auth::guard('agent')->id())
            ->firstOrFail();
    }

    private function resolveSessionDurationSeconds(Reservation $reservation): int
    {
        if (! $reservation->checked_in_at) {
            return self::DEFAULT_SESSION_DURATION_SECONDS;
        }

        $reservationTime = $this->resolveReservationTime($reservation);
        $checkedInAt = Carbon::parse($reservation->checked_in_at);

        if (! $checkedInAt->greaterThan($reservationTime)) {
            return self::DEFAULT_SESSION_DURATION_SECONDS;
        }

        $lateSeconds = $reservationTime->diffInSeconds($checkedInAt);

        return max(0, self::DEFAULT_SESSION_DURATION_SECONDS - $lateSeconds);
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
}
