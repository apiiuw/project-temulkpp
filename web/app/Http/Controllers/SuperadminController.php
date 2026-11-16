<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsRatingDashboard;
use App\Models\Agent;
use App\Models\Pimpinan;
use App\Models\Reservation;
use App\Models\Menu;
use App\Models\RoleMenu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SuperadminController extends Controller
{
    use BuildsRatingDashboard;

    private const MAX_EXPORT_ROWS = 100;
    private const ONGOING_STATUSES = ['pending', 'checked_in_front_desk', 'in_progress', 'dialihkan_ke_l2'];

    public function dashboard(): View
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

        return view('roles.superadmin.dashboard.index', [
            'title' => 'Dashboard Superadmin',
            'jenisData' => $jenis,
            'completedCount' => Reservation::query()->jenisData($jenis)->where('status', 'completed')->count(),
            'unfinishedCount' => Reservation::query()->jenisData($jenis)->whereIn('status', self::ONGOING_STATUSES)->count(),
            'completedReservations' => $completedReservations,
            'unfinishedReservations' => $unfinishedReservations,
            'agentPerformance' => $agentPerformance,
            ...$this->ratingDashboardData(),
        ]);
    }

    public function masterAgent(Request $request): View
    {
        $validatedFilters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
        $search = trim((string) ($validatedFilters['q'] ?? ''));
        $status = $validatedFilters['status'] ?? null;
        
        $agentsQuery = Agent::query();
        
        if ($search !== '') {
            $agentsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($status !== null && $status !== '') {
            $agentsQuery->where('is_active', $status === 'active');
        }
        
        $agents = $agentsQuery->latest('id')->paginate(5)->withQueryString();
        $activeAgentCount = Agent::query()->where('is_active', true)->count();
        $inactiveAgentCount = Agent::query()->where('is_active', false)->count();
        
        return view('roles.superadmin.master-data.agent', [
            'title' => 'Master Data Agent',
            'agents' => $agents,
            'activeAgentCount' => $activeAgentCount,
            'inactiveAgentCount' => $inactiveAgentCount,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ]
        ]);
    }

    public function storeAgent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:agents',
            'password' => 'required|string|min:8',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        Agent::create($validated);

        return redirect()->back()->with('success', 'Agent berhasil ditambahkan.');
    }

    public function updateAgent(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:agents,email,' . $agent->id,
            'password' => 'nullable|string|min:8',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $agent->update($validated);

        return redirect()->back()->with('success', 'Agent berhasil diperbarui.');
    }

    public function toggleAgentStatus(Agent $agent)
    {
        $agent->is_active = !$agent->is_active;
        $agent->save();

        return redirect()->back()->with('success', 'Status agent berhasil diubah.');
    }


    public function masterPimpinan(Request $request): View
    {
        $validatedFilters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
        $search = trim((string) ($validatedFilters['q'] ?? ''));
        $status = $validatedFilters['status'] ?? null;
        
        $pimpinansQuery = Pimpinan::query();
        
        if ($search !== '') {
            $pimpinansQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($status !== null && $status !== '') {
            $pimpinansQuery->where('is_active', $status === 'active');
        }
        
        $pimpinans = $pimpinansQuery->latest('id')->paginate(5)->withQueryString();
        $activePimpinanCount = Pimpinan::query()->where('is_active', true)->count();
        $inactivePimpinanCount = Pimpinan::query()->where('is_active', false)->count();
        
        return view('roles.superadmin.master-data.pimpinan', [
            'title' => 'Master Data Pimpinan',
            'pimpinans' => $pimpinans,
            'activePimpinanCount' => $activePimpinanCount,
            'inactivePimpinanCount' => $inactivePimpinanCount,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ]
        ]);
    }

    public function storePimpinan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pimpinans',
            'password' => 'required|string|min:8',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        Pimpinan::create($validated);

        return redirect()->back()->with('success', 'Pimpinan berhasil ditambahkan.');
    }

    public function updatePimpinan(Request $request, Pimpinan $pimpinan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pimpinans,email,' . $pimpinan->id,
            'password' => 'nullable|string|min:8',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $pimpinan->update($validated);

        return redirect()->back()->with('success', 'Pimpinan berhasil diperbarui.');
    }

    public function togglePimpinanStatus(Pimpinan $pimpinan)
    {
        $pimpinan->is_active = !$pimpinan->is_active;
        $pimpinan->save();

        return redirect()->back()->with('success', 'Status pimpinan berhasil diubah.');
    }

    public function permissions(): View
    {
        $menus = Menu::all()->groupBy('category');
        $roles = ['agent', 'pimpinan', 'superadmin'];
        
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role] = RoleMenu::where('role', $role)->pluck('menu_id')->toArray();
        }

        return view('roles.superadmin.permissions.index', [
            'title' => 'Perizinan Menu',
            'menus' => $menus,
            'roles' => $roles,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function updatePermissions(Request $request)
    {
        $roles = ['agent', 'pimpinan', 'superadmin'];
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.agent' => ['nullable', 'array'],
            'permissions.pimpinan' => ['nullable', 'array'],
            'permissions.superadmin' => ['nullable', 'array'],
            'permissions.*.*' => ['integer', 'distinct', 'exists:menus,id'],
        ]);

        $permissions = $validated['permissions'] ?? [];

        DB::transaction(function () use ($roles, $permissions): void {
            foreach ($roles as $role) {
                RoleMenu::query()->where('role', $role)->delete();

                $menuIds = array_values(array_unique(array_map(
                    'intval',
                    $permissions[$role] ?? []
                )));

                foreach ($menuIds as $menuId) {
                    RoleMenu::create([
                        'role' => $role,
                        'menu_id' => $menuId,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Perizinan menu berhasil diperbarui.');
    }

    public function completedServices(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
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

        return view('roles.superadmin.services.completed', [
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
            'q' => ['nullable', 'string', 'max:255'],
            'agent_id' => ['nullable', 'integer', 'exists:agents,id'],
            'status' => ['nullable', 'in:pending,checked_in_front_desk,in_progress,dialihkan_ke_l2'],
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

        if ($dateFrom) {
            $ongoingQuery->whereDate('tanggal_jam', '>=', $dateFrom);
        }

        if ($dateTo) {
            $ongoingQuery->whereDate('tanggal_jam', '<=', $dateTo);
        }

        $statusCountsQuery = clone $ongoingQuery;
        if ($status !== null && $status !== '') {
            $ongoingQuery->where('status', $status);
        }

        $ongoingReservations = (clone $ongoingQuery)
            ->latest('tanggal_jam')
            ->paginate(15)
            ->withQueryString();

        $statusCounts = $statusCountsQuery
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('roles.superadmin.services.ongoing', [
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
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'only_active' => ['nullable', 'boolean'],
            'min_completed' => ['nullable', 'integer', 'min:0'],
        ]);
        $search = trim((string) ($validated['q'] ?? ''));
        $onlyActive = $request->boolean('only_active');
        $minCompleted = (int) ($validated['min_completed'] ?? 0);

        $agentQuery = Agent::query();

        if ($search !== '') {
            $agentQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($onlyActive) {
            $agentQuery->where('is_active', true);
        }

        // Badge "Agent Aktif" dihitung dari query bersih (sebelum withCount/
        // having dipasang) agar angkanya akurat — 16/16 saat tanpa filter.
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
            ->when($minCompleted > 0, fn ($query) => $query->having('completed_reservations_count', '>=', $minCompleted))
            ->orderBy('id')
            ->get();

        return view('roles.superadmin.services.agent-performance', [
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
        $filters = $this->validateReportFilters($request);
        $reportQuery = $this->buildReportQuery($filters);

        $reportRows = (clone $reportQuery)
            ->latest('tanggal_jam')
            ->paginate(20)
            ->withQueryString();

        $baseSummaryQuery = (clone $reportQuery);

        return view('roles.superadmin.services.report', [
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
            'filters' => $filters,
        ]);
    }

    public function printReport(Request $request)
    {
        $filters = $this->validateReportFilters($request);
        $reportQuery = $this->buildReportQuery($filters);

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
            'filters' => $filters,
            'agents' => Agent::optionsSortedByName(),
        ];

        return Pdf::loadView('roles.pimpinan.services.report-print', $data)
            ->setPaper('a4', 'portrait')
            ->stream('Laporan-Layanan-' . now()->format('YmdHis') . '.pdf');
    }

    /** @return array{q: string, agent_id: int|null, status: string|null, jenis_layanan: string|null, date_from: string|null, date_to: string|null} */
    private function validateReportFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'agent_id' => ['nullable', 'integer', 'exists:agents,id'],
            'status' => ['nullable', 'in:pending,checked_in_front_desk,in_progress,completed,expired_front_desk,dialihkan_ke_l2'],
            'jenis_layanan' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'agent_id' => isset($validated['agent_id']) ? (int) $validated['agent_id'] : null,
            'status' => $validated['status'] ?? null,
            'jenis_layanan' => $validated['jenis_layanan'] ?? null,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
        ];
    }

    /** @param array{q: string, agent_id: int|null, status: string|null, jenis_layanan: string|null, date_from: string|null, date_to: string|null} $filters */
    private function buildReportQuery(array $filters): Builder
    {
        $search = $filters['q'];
        $agentId = $filters['agent_id'];
        $status = $filters['status'];
        $serviceType = $filters['jenis_layanan'];
        $dateFrom = $filters['date_from'];
        $dateTo = $filters['date_to'];

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
