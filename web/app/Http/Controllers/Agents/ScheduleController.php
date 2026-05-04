<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $agentId = Auth::guard('agent')->id();
        $today = Carbon::today();

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $baseQuery = Reservation::query()
            ->where('agent_id', $agentId);

        if ($filters['q'] !== '') {
            $search = $filters['q'];

            $baseQuery->where(function ($query) use ($search): void {
                $query->where('kode_reservasi', 'like', '%' . $search . '%')
                    ->orWhere('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhere('asal_pt', 'like', '%' . $search . '%')
                    ->orWhere('jabatan', 'like', '%' . $search . '%')
                    ->orWhere('jenis_layanan', 'like', '%' . $search . '%');
            });
        }

        if ($filters['status'] !== '') {
            $baseQuery->where('status', $filters['status']);
        }

        if ($filters['date_from'] !== '') {
            $baseQuery->whereDate('tanggal_jam', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $baseQuery->whereDate('tanggal_jam', '<=', $filters['date_to']);
        }

        $filteredReservations = (clone $baseQuery)
            ->orderBy('tanggal_jam')
            ->get();

        $todayReservations = $filteredReservations
            ->filter(fn (Reservation $reservation) => Carbon::parse($reservation->tanggal_jam)->isSameDay($today))
            ->values();

        $upcomingReservations = $filteredReservations
            ->filter(fn (Reservation $reservation) => Carbon::parse($reservation->tanggal_jam)->greaterThanOrEqualTo($today->copy()->startOfDay()))
            ->sortBy('tanggal_jam')
            ->values()
            ->take(20);

        return view('roles.agents.schedule.index', [
            'title' => 'Jadwal Layanan Agent',
            'todayReservations' => $todayReservations,
            'upcomingReservations' => $upcomingReservations,
            'filters' => $filters,
        ]);
    }
}
