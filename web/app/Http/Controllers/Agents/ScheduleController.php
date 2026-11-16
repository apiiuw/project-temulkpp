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

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,checked_in_front_desk,in_progress,completed,expired_front_desk,dialihkan_ke_l2'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $filters = [
            'q' => trim((string) ($validated['q'] ?? '')),
            'status' => $validated['status'] ?? '',
            'date_from' => $validated['date_from'] ?? '',
            'date_to' => $validated['date_to'] ?? '',
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

        $todayReservations = (clone $baseQuery)
            ->whereDate('tanggal_jam', $today)
            ->orderBy('tanggal_jam')
            ->get();

        $upcomingReservations = (clone $baseQuery)
            ->where('tanggal_jam', '>=', Carbon::now())
            ->orderBy('tanggal_jam')
            ->paginate(20)
            ->withQueryString();

        return view('roles.agents.schedule.index', [
            'title' => 'Jadwal Layanan Agent',
            'todayReservations' => $todayReservations,
            'upcomingReservations' => $upcomingReservations,
            'filters' => $filters,
        ]);
    }
}
