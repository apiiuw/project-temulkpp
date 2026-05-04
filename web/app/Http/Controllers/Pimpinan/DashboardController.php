<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    private const ONGOING_STATUSES = ['pending', 'checked_in_front_desk', 'in_progress'];

    public function index(): View
    {
        $completedReservations = Reservation::query()
            ->where('status', 'completed')
            ->latest('waktu_selesai_tatap_muka')
            ->limit(5)
            ->get();

        $unfinishedReservations = Reservation::query()
            ->whereIn('status', self::ONGOING_STATUSES)
            ->latest('tanggal_jam')
            ->limit(5)
            ->get();


        $agentPerformance = Agent::query()
            ->withCount([
                'reservations as total_reservations_count',
                'reservations as completed_reservations_count' => fn ($query) => $query->where('status', 'completed'),
                'reservations as active_reservations_count' => fn ($query) => $query->whereIn('status', self::ONGOING_STATUSES),
            ])
            ->orderByDesc('completed_reservations_count')
            ->orderByDesc('total_reservations_count')
            ->limit(5)
            ->get();

        return view('roles.pimpinan.dashboard.index', [
            'title' => 'Dashboard Pimpinan',
            'completedCount' => Reservation::query()->where('status', 'completed')->count(),
            'unfinishedCount' => Reservation::query()->whereIn('status', self::ONGOING_STATUSES)->count(),
            'completedReservations' => $completedReservations,
            'unfinishedReservations' => $unfinishedReservations,
            'agentPerformance' => $agentPerformance,
        ]);
    }

    public function completedServices(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $agentId = $request->query('agent_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $completedQuery = Reservation::query()
            ->with('agent:id,name')
            ->where('status', 'completed');

        if ($search !== '') {
            $completedQuery->where(function ($query) use ($search) {
                $query
                    ->where('kode_reservasi', 'like', '%' . $search . '%')
                    ->orWhere('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhereHas('agent', fn ($agentQuery) => $agentQuery->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($agentId !== null && $agentId !== '') {
            $completedQuery->where('agent_id', (int) $agentId);
        }

        if ($dateFrom) {
            $completedQuery->whereDate('waktu_selesai_tatap_muka', '>=', $dateFrom);
        }

        if ($dateTo) {
            $completedQuery->whereDate('waktu_selesai_tatap_muka', '<=', $dateTo);
        }

        $completedReservations = (clone $completedQuery)
            ->latest('waktu_selesai_tatap_muka')
            ->paginate(15)
            ->withQueryString();

        return view('roles.pimpinan.services.completed', [
            'title' => 'Rekap Layanan Selesai',
            'completedCount' => (clone $completedQuery)->count(),
            'completedReservations' => $completedReservations,
            'agents' => Agent::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'q' => $search,
                'agent_id' => $agentId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function ongoingServices(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $agentId = $request->query('agent_id');
        $status = $request->query('status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ongoingQuery = Reservation::query()
            ->with('agent:id,name')
            ->whereIn('status', self::ONGOING_STATUSES);

        if ($search !== '') {
            $ongoingQuery->where(function ($query) use ($search) {
                $query
                    ->where('kode_reservasi', 'like', '%' . $search . '%')
                    ->orWhere('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhereHas('agent', fn ($agentQuery) => $agentQuery->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($agentId !== null && $agentId !== '') {
            $ongoingQuery->where('agent_id', (int) $agentId);
        }

        if ($status !== null && $status !== '' && in_array($status, self::ONGOING_STATUSES, true)) {
            $ongoingQuery->where('status', $status);
        }

        if ($dateFrom) {
            $ongoingQuery->whereDate('tanggal_jam', '>=', $dateFrom);
        }

        if ($dateTo) {
            $ongoingQuery->whereDate('tanggal_jam', '<=', $dateTo);
        }

        $ongoingReservations = (clone $ongoingQuery)
            ->latest('tanggal_jam')
            ->paginate(15)
            ->withQueryString();

        $statusCounts = (clone $ongoingQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('roles.pimpinan.services.ongoing', [
            'title' => 'Monitoring Layanan Berjalan',
            'ongoingTotal' => (clone $ongoingQuery)->count(),
            'statusCounts' => $statusCounts,
            'ongoingReservations' => $ongoingReservations,
            'agents' => Agent::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'q' => $search,
                'agent_id' => $agentId,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }


    public function agentPerformance(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $onlyActive = $request->boolean('only_active');
        $minCompleted = (int) $request->query('min_completed', 0);

        $agentQuery = Agent::query();

        if ($search !== '') {
            $agentQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($onlyActive) {
            $agentQuery->whereHas('reservations', fn ($query) => $query->whereIn('status', self::ONGOING_STATUSES));
        }

        $agentPerformance = $agentQuery
            ->withCount([
                'reservations as total_reservations_count',
                'reservations as completed_reservations_count' => fn ($query) => $query->where('status', 'completed'),
                'reservations as active_reservations_count' => fn ($query) => $query->whereIn('status', self::ONGOING_STATUSES),
            ])
            ->having('completed_reservations_count', '>=', $minCompleted)
            ->orderByDesc('completed_reservations_count')
            ->orderByDesc('total_reservations_count')
            ->paginate(15)
            ->withQueryString();

        return view('roles.pimpinan.services.agent-performance', [
            'title' => 'Performa Agent',
            'agentPerformance' => $agentPerformance,
            'totalAgents' => (clone $agentQuery)->count(),
            'activeAgents' => (clone $agentQuery)
                ->whereHas('reservations', fn ($query) => $query->whereIn('status', self::ONGOING_STATUSES))
                ->count(),
            'filters' => [
                'q' => $search,
                'only_active' => $onlyActive,
                'min_completed' => $minCompleted,
            ],
        ]);
    }

    public function report(Request $request): View
    {
        $reportQuery = $this->buildReportQuery($request);

        $reportRows = (clone $reportQuery)
            ->latest('tanggal_jam')
            ->paginate(20)
            ->withQueryString();

        $baseSummaryQuery = (clone $reportQuery);

        return view('roles.pimpinan.services.report', [
            'title' => 'Laporan Layanan',
            'reportRows' => $reportRows,
            'agents' => Agent::query()->orderBy('name')->get(['id', 'name']),
            'serviceTypes' => Reservation::query()
                ->whereNotNull('jenis_layanan')
                ->select('jenis_layanan')
                ->distinct()
                ->orderBy('jenis_layanan')
                ->pluck('jenis_layanan'),
            'summary' => [
                'total' => (clone $baseSummaryQuery)->count(),
                'completed' => (clone $baseSummaryQuery)->where('status', 'completed')->count(),
                'ongoing' => (clone $baseSummaryQuery)->whereIn('status', self::ONGOING_STATUSES)->count(),
            ],
            'filters' => [
                'q' => trim((string) $request->query('q', '')),
                'agent_id' => $request->query('agent_id'),
                'status' => $request->query('status'),
                'jenis_layanan' => $request->query('jenis_layanan'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
            ],
        ]);
    }
    public function printReport(Request $request)
    {
        $reportQuery = $this->buildReportQuery($request);

        $rows = (clone $reportQuery)
            ->latest('tanggal_jam')
            ->get();

        $data = [
            'title' => 'Cetak Laporan Layanan',
            'rows' => $rows,
            'printedAt' => now(),
            'filters' => [
                'q' => trim((string) $request->query('q', '')),
                'agent_id' => $request->query('agent_id'),
                'status' => $request->query('status'),
                'jenis_layanan' => $request->query('jenis_layanan'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
            ],
            'agents' => Agent::query()->orderBy('name')->get(['id', 'name']),
        ];

        return Pdf::loadView('roles.pimpinan.services.report-print', $data)
            ->setPaper('a4', 'portrait')
            ->download('Laporan-Layanan-' . now()->format('YmdHis') . '.pdf');
    }

    private function buildReportQuery(Request $request): Builder
    {
        $search = trim((string) $request->query('q', ''));
        $agentId = $request->query('agent_id');
        $status = $request->query('status');
        $serviceType = $request->query('jenis_layanan');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = Reservation::query()->with('agent:id,name');

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery
                    ->where('kode_reservasi', 'like', '%' . $search . '%')
                    ->orWhere('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhere('jenis_layanan', 'like', '%' . $search . '%')
                    ->orWhereHas('agent', fn ($agentQuery) => $agentQuery->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($agentId !== null && $agentId !== '') {
            $query->where('agent_id', (int) $agentId);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($serviceType !== null && $serviceType !== '') {
            $query->where('jenis_layanan', $serviceType);
        }

        if ($dateFrom) {
            $query->whereDate('tanggal_jam', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('tanggal_jam', '<=', $dateTo);
        }

        return $query;
    }
}
