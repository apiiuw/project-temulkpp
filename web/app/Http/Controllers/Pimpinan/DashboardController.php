<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\BuildsRatingDashboard;
use App\Models\Agent;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

final class DashboardController extends Controller
{
    use BuildsRatingDashboard;

    private const PROCESS_STATUSES = ['pending', 'checked_in_front_desk', 'in_progress'];
    private const UNFINISHED_TEST_STATUSES = ['dialihkan_ke_l2', 'expired_front_desk'];
    private const ONGOING_STATUSES = ['pending', 'checked_in_front_desk', 'in_progress', 'dialihkan_ke_l2'];
    private const VALID_STATUSES = [
        'pending',
        'checked_in_front_desk',
        'in_progress',
        'completed',
        'expired_front_desk',
        'dialihkan_ke_l2',
    ];
    private const MAX_EXPORT_ROWS = 100;

    public function index(): View
    {
        $jenis = request()->query('data') === 'uji' ? 'uji' : 'historis';

        $completedReservations = Reservation::query()
            ->jenisData($jenis)
            ->where('status', 'completed')
            ->latest('waktu_selesai_tatap_muka')
            ->limit(5)
            ->get();

        $unfinishedReservations = Reservation::query()
            ->jenisData($jenis)
            ->whereIn('status', self::ONGOING_STATUSES)
            ->latest('tanggal_jam')
            ->limit(5)
            ->get();


        $agentPerformance = Agent::query()
            ->withCount([
                'reservations as total_reservations_count' => fn ($query) => $query->jenisData($jenis),
                'reservations as completed_reservations_count' => fn ($query) => $query->jenisData($jenis)->where('status', 'completed'),
                'reservations as active_reservations_count' => fn ($query) => $query->jenisData($jenis)->whereIn('status', self::ONGOING_STATUSES),
            ])
            ->orderByDesc('completed_reservations_count')
            ->limit(5)
            ->get();

        return view('roles.pimpinan.dashboard.index', [
            'title' => 'Dashboard Pimpinan',
            'jenisData' => $jenis,
            'completedCount' => Reservation::query()->jenisData($jenis)->where('status', 'completed')->count(),
            'inProgressCount' => Reservation::query()->jenisData($jenis)->whereIn('status', self::PROCESS_STATUSES)->count(),
            'unfinishedCount' => $jenis === 'uji'
                ? Reservation::query()->jenisData($jenis)->whereIn('status', self::UNFINISHED_TEST_STATUSES)->count()
                : Reservation::query()->jenisData($jenis)->where('status', 'dialihkan_ke_l2')->count(),
            'completedReservations' => $completedReservations,
            'unfinishedReservations' => $unfinishedReservations,
            'agentPerformance' => $agentPerformance,
            ...$this->ratingDashboardData(),
        ]);
    }

    public function completedServices(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'agent_id' => ['nullable', 'integer', 'exists:agents,id'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);
        $search = trim((string) ($filters['q'] ?? ''));
        $agentId = $filters['agent_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

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
            'agents' => Agent::optionsSortedByName(),
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
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'agent_id' => ['nullable', 'integer', 'exists:agents,id'],
            'status' => ['nullable', 'string', 'in:' . implode(',', self::ONGOING_STATUSES)],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);
        $search = trim((string) ($filters['q'] ?? ''));
        $agentId = $filters['agent_id'] ?? null;
        $status = $filters['status'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

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
            'agents' => Agent::optionsSortedByName(),
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
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'only_active' => ['nullable', 'boolean'],
            'min_completed' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);
        $search = trim((string) ($filters['q'] ?? ''));
        $onlyActive = $request->boolean('only_active');
        $minCompleted = (int) ($filters['min_completed'] ?? 0);

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

        if ($minCompleted > 0) {
            $agentQuery->whereHas(
                'reservations',
                fn ($query) => $query->where('status', 'completed'),
                '>=',
                $minCompleted,
            );
        }

        // Badge "Agent Aktif" dihitung dari query bersih (sebelum withCount
        // dipasang) agar angkanya akurat — 16/16 saat tanpa filter.
        $totalAgents = (clone $agentQuery)->count();
        $activeAgents = (clone $agentQuery)->where('is_active', true)->count();

        $averageRatingSub = Reservation::query()
            ->selectRaw('AVG((rating_kemudahan + rating_efisiensi + rating_sikap + rating_kejelasan + rating_kepuasan) / ' . count(Reservation::RATING_QUESTIONS) . ')')
            ->whereColumn('agents.id', 'reservations.agent_id');

        $agentPerformance = $agentQuery
            ->withCount([
                'reservations as total_reservations_count',
                'reservations as completed_reservations_count' => fn ($query) => $query->where('status', 'completed'),
                'reservations as active_reservations_count' => fn ($query) => $query->whereIn('status', self::ONGOING_STATUSES),
                'reservations as rated_reservations_count' => fn ($query) => $query
                    ->whereNotNull('rating_kemudahan')
                    ->whereNotNull('rating_efisiensi')
                    ->whereNotNull('rating_sikap')
                    ->whereNotNull('rating_kejelasan')
                    ->whereNotNull('rating_kepuasan'),
            ])
            ->selectSub($averageRatingSub, 'average_rating')
            ->orderBy('id')
            ->get();

        return view('roles.pimpinan.services.agent-performance', [
            'title' => 'Performa Agent',
            'agentPerformance' => $agentPerformance,
            'totalAgents' => $totalAgents,
            'activeAgents' => $activeAgents,
            'filters' => [
                'q' => $search,
                'only_active' => $onlyActive,
                'min_completed' => $minCompleted,
            ],
        ]);
    }

    public function report(Request $request): View
    {
        $this->validateReportFilters($request);
        $reportQuery = $this->buildReportQuery($request);

        $reportRows = (clone $reportQuery)
            ->latest('tanggal_jam')
            ->paginate(20)
            ->withQueryString();

        $baseSummaryQuery = (clone $reportQuery);

        return view('roles.pimpinan.services.report', [
            'title' => 'Laporan Layanan',
            'reportRows' => $reportRows,
            'agents' => Agent::optionsSortedByName(),
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
    public function printReport(Request $request): Response
    {
        $this->validateReportFilters($request);
        $reportQuery = $this->buildReportQuery($request);

        $rows = (clone $reportQuery)
            ->select([
                'id',
                'kode_reservasi',
                'nama_lengkap',
                'jenis_layanan',
                'agent_id',
                'status',
                'tanggal_jam',
            ])
            ->latest('tanggal_jam')
            ->limit(self::MAX_EXPORT_ROWS)
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
            'agents' => Agent::optionsSortedByName(),
        ];

        return Pdf::loadView('roles.pimpinan.services.report-print', $data)
            ->setPaper('a4', 'portrait')
            ->stream('Laporan-Layanan-' . now()->format('YmdHis') . '.pdf');
    }

    private function validateReportFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'agent_id' => ['nullable', 'integer', 'exists:agents,id'],
            'status' => ['nullable', 'string', 'in:' . implode(',', self::VALID_STATUSES)],
            'jenis_layanan' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);
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
