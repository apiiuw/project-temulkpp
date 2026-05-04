<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Pimpinan;
use App\Models\Reservation;
use App\Models\Menu;
use App\Models\RoleMenu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class SuperadminController extends Controller
{
    private const ONGOING_STATUSES = ['pending', 'checked_in_front_desk', 'in_progress'];

    public function dashboard(): View
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

        return view('roles.superadmin.dashboard.index', [
            'title' => 'Dashboard Superadmin',
            'completedCount' => Reservation::query()->where('status', 'completed')->count(),
            'unfinishedCount' => Reservation::query()->whereIn('status', self::ONGOING_STATUSES)->count(),
            'completedReservations' => $completedReservations,
            'unfinishedReservations' => $unfinishedReservations,
            'agentPerformance' => $agentPerformance,
        ]);
    }

    public function masterAgent(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        
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
        
        $agents = $agentsQuery->latest()->paginate(20)->withQueryString();
        
        return view('roles.superadmin.master-data.agent', [
            'title' => 'Master Data Agent',
            'agents' => $agents,
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
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        
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
        
        $pimpinans = $pimpinansQuery->latest()->paginate(20)->withQueryString();
        
        return view('roles.superadmin.master-data.pimpinan', [
            'title' => 'Master Data Pimpinan',
            'pimpinans' => $pimpinans,
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
        $permissions = $request->input('permissions', []); // [role => [menu_ids]]
        
        // Reset and rebuild permissions
        foreach (['agent', 'pimpinan', 'superadmin'] as $role) {
            RoleMenu::where('role', $role)->delete();
            
            if (isset($permissions[$role])) {
                foreach ($permissions[$role] as $menuId) {
                    RoleMenu::create([
                        'role' => $role,
                        'menu_id' => $menuId
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Perizinan menu berhasil diperbarui.');
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

        return view('roles.superadmin.services.completed', [
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

        return view('roles.superadmin.services.ongoing', [
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

        return view('roles.superadmin.services.agent-performance', [
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

        return view('roles.superadmin.services.report', [
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
