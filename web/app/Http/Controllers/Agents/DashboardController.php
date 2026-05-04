<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today()->toDateString();
        $agentId = Auth::guard('agent')->id();

        $pendingReservations = Reservation::whereDate('tanggal_jam', $today)
            ->where('status', 'pending')
            ->orderBy('tanggal_jam', 'asc')
            ->get();

        $myReservations = Reservation::whereDate('tanggal_jam', $today)
            ->where('agent_id', $agentId)
            ->orderBy('tanggal_jam', 'asc')
            ->get();

        $readyReservations = Reservation::query()
            ->where('agent_id', $agentId)
            ->whereDate('tanggal_jam', $today)
            ->where('status', 'checked_in_front_desk')
            ->orderBy('tanggal_jam', 'asc')
            ->get();

        $ongoingReservations = Reservation::query()
            ->where('agent_id', $agentId)
            ->whereDate('tanggal_jam', $today)
            ->where('status', 'in_progress')
            ->orderBy('tanggal_jam', 'asc')
            ->get();

        $taskSummary = [
            'todayTotal' => Reservation::query()
                ->where('agent_id', $agentId)
                ->whereDate('tanggal_jam', $today)
                ->count(),
            'needsAction' => Reservation::query()
                ->where('agent_id', $agentId)
                ->whereDate('tanggal_jam', $today)
                ->whereIn('status', ['pending', 'checked_in_front_desk'])
                ->count(),
            'inProgress' => Reservation::query()
                ->where('agent_id', $agentId)
                ->whereDate('tanggal_jam', $today)
                ->where('status', 'in_progress')
                ->count(),
            'completedToday' => Reservation::query()
                ->where('agent_id', $agentId)
                ->whereDate('tanggal_jam', $today)
                ->where('status', 'completed')
                ->count(),
        ];

        return view('roles.agents.dashboard.index', [
            'title' => 'Dashboard Agent',
            'pendingReservations' => $pendingReservations,
            'myReservations' => $myReservations,
            'readyReservations' => $readyReservations,
            'ongoingReservations' => $ongoingReservations,
            'taskSummary' => $taskSummary,
        ]);
    }
}
