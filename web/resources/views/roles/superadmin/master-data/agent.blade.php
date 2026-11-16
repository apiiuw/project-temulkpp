@extends('layouts.superadmin')

@section('container')
<div class="min-h-screen bg-stone-50/50 px-4 py-8 md:px-8">
    <div class="mx-auto max-w-7xl">
        {{-- Header Section --}}
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-stone-900 md:text-3xl">Kelola Data Agent</h1>
                <p class="mt-1 text-sm font-medium text-stone-500">Manajemen akun petugas layanan (Agent) dalam sistem.</p>
            </div>
            <div>
                <button onclick="openModal('addAgentModal')" class="inline-flex items-center gap-2 rounded-2xl bg-amber-900 px-6 py-3 text-sm font-bold text-white transition-all hover:bg-amber-800 hover:shadow-lg active:scale-95">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Agent Baru
                </button>
            </div>
        </div>

        {{-- Stats Overview (Optional but nice) --}}
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-400">Total Agent</p>
                <p class="mt-2 text-3xl font-black text-stone-900">{{ $agents->total() }}</p>
            </div>
            <div class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-400">Agent Aktif</p>
                <p class="mt-2 text-3xl font-black text-green-600">{{ $activeAgentCount }}</p>
            </div>
            <div class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-400">Non-Aktif</p>
                <p class="mt-2 text-3xl font-black text-stone-400">{{ $inactiveAgentCount }}</p>
            </div>
        </div>

        {{-- Main Tabs: Daftar Agent vs Jadwal Shift Ganjil/Genap --}}
        <div class="mb-6 inline-flex rounded-2xl border border-stone-200 bg-white p-1.5 shadow-sm">
            <a href="{{ route('superadmin.master-agent', ['tab' => 'agents']) }}"
               class="rounded-xl px-5 py-2.5 text-sm font-bold transition-all {{ $activeTab === 'agents' ? 'bg-stone-900 text-white shadow-sm' : 'text-stone-600 hover:text-stone-900' }}">
                Daftar Agent
            </a>
            <a href="{{ route('superadmin.master-agent', ['tab' => 'schedules', 'parity' => $selectedParity]) }}"
               class="rounded-xl px-5 py-2.5 text-sm font-bold transition-all {{ $activeTab === 'schedules' ? 'bg-amber-900 text-white shadow-sm' : 'text-stone-600 hover:text-stone-900' }}">
                Jadwal Shift (Ganjil &amp; Genap)
            </a>
        </div>

        @if($activeTab === 'agents')
        {{-- Filter Section --}}
        <div class="mb-6 rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
            <form method="GET" action="{{ route('superadmin.master-agent') }}" class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="flex-1">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Cari Agent</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Nama atau Email..." class="w-full rounded-2xl border-stone-200 bg-stone-50 py-3 pl-11 pr-4 text-sm focus:border-amber-500 focus:ring-amber-500" maxlength="255">
                    </div>
                </div>
                <div class="w-full md:w-48">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Status</label>
                    <select name="status" class="w-full rounded-2xl border-stone-200 bg-stone-50 py-3 text-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="w-full md:w-48">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Urutkan</label>
                    <select name="sort" class="w-full rounded-2xl border-stone-200 bg-stone-50 py-3 text-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="name_asc" {{ ($filters['sort'] ?? 'name_asc') === 'name_asc' ? 'selected' : '' }}>Nama (A–Z)</option>
                        <option value="name_desc" {{ ($filters['sort'] ?? 'name_asc') === 'name_desc' ? 'selected' : '' }}>Nama (Z–A)</option>
                        <option value="latest" {{ ($filters['sort'] ?? 'name_asc') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-2xl bg-stone-900 px-6 py-3 text-sm font-bold text-white transition-all hover:bg-stone-800 active:scale-95">
                        Filter
                    </button>
                    @if($filters['q'] || $filters['status'] || ($filters['sort'] ?? 'name_asc') !== 'name_asc')
                        <a href="{{ route('superadmin.master-agent') }}" class="rounded-2xl bg-stone-100 px-6 py-3 text-sm font-bold text-stone-600 transition-all hover:bg-stone-200 active:scale-95">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Section --}}
        <div class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-stone-100 bg-stone-50/50">
                            <th class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-stone-400">Agent</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-stone-400">Status</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-stone-400">Jadwal Minggu Ini</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-stone-400">Terdaftar</th>
                            <th class="px-6 py-5 text-right text-xs font-bold uppercase tracking-widest text-stone-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($agents as $agent)
                            <tr class="group transition-colors hover:bg-stone-50/50">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-900 font-bold">
                                            {{ substr($agent->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-stone-900">{{ $agent->name }}</p>
                                            <p class="text-xs text-stone-500">{{ $agent->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($agent->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-500">
                                            <span class="h-1.5 w-1.5 rounded-full bg-stone-400"></span>
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $weekShifts = $scheduleMatrix[$currentParity] ?? [];
                                    @endphp
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(\App\Models\AgentSchedule::OPERATIONAL_DAYS as $dayNum => $dayName)
                                            @php
                                                $shift = $weekShifts[$dayNum][$agent->id] ?? 'off';
                                                $shortDay = mb_substr($dayName, 0, 3);
                                            @endphp
                                            @if($shift === 'morning')
                                                <span class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-800">{{ $shortDay }}: Pagi</span>
                                            @elseif($shift === 'afternoon')
                                                <span class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-2 py-0.5 text-[11px] font-bold text-sky-800">{{ $shortDay }}: Siang</span>
                                            @endif
                                        @endforeach
                                        @if(collect(\App\Models\AgentSchedule::OPERATIONAL_DAYS)->every(fn ($n, $d) => ($weekShifts[$d][$agent->id] ?? 'off') === 'off'))
                                            <span class="text-xs font-medium text-stone-400">Belum terjadwal</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-medium text-stone-600">{{ $agent->created_at->format('d M Y') }}</p>
                                    <p class="text-[10px] text-stone-400 uppercase tracking-tighter">{{ $agent->created_at->format('H:i') }} WIB</p>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button 
                                            onclick='openEditModal(@json($agent))'
                                            class="rounded-xl border border-stone-200 bg-white p-2 text-stone-600 transition-all hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 shadow-sm"
                                            title="Edit Agent"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('superadmin.master-agent.toggle-status', $agent) }}" method="POST" class="inline" onsubmit="return confirm('{{ $agent->is_active ? 'Nonaktifkan agent ini?' : 'Aktifkan agent ini?' }}')">
                                            @csrf
                                            @method('PATCH')
                                            <button 
                                                type="submit"
                                                class="rounded-xl border border-stone-200 bg-white p-2 text-stone-600 transition-all {{ $agent->is_active ? 'hover:border-red-300 hover:bg-red-50 hover:text-red-700' : 'hover:border-green-300 hover:bg-green-50 hover:text-green-700' }} shadow-sm"
                                                title="{{ $agent->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            >
                                                @if($agent->is_active)
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                @else
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="mb-4 rounded-full bg-stone-100 p-4">
                                            <svg class="h-10 w-10 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                        </div>
                                        <p class="text-lg font-bold text-stone-900">Tidak ada agent ditemukan</p>
                                        <p class="text-sm text-stone-500">Coba ubah kata kunci pencarian atau filter Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="border-t border-stone-100 bg-stone-50/50 px-6 py-4">
                @include('partials.pagination', ['paginator' => $agents])
            </div>
        </div>
        @else
        {{-- Jadwal Shift Tab (Minggu Ganjil & Minggu Genap) --}}
        <div class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-stone-100 bg-stone-50/60 p-6 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach(\App\Models\AgentSchedule::PARITIES as $parityKey => $parityLabel)
                        <a href="{{ route('superadmin.master-agent', ['tab' => 'schedules', 'parity' => $parityKey]) }}"
                           class="inline-flex items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-bold transition-all {{ $selectedParity === $parityKey ? 'border-amber-900 bg-amber-900 text-white shadow-sm' : 'border-stone-200 bg-white text-stone-700 hover:bg-stone-100' }}">
                            <span>{{ $parityLabel }}</span>
                            @if($currentParity === $parityKey)
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider {{ $selectedParity === $parityKey ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700' }}">
                                    Minggu Ini
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <form action="{{ route('superadmin.master-agent.schedules.generate') }}" method="POST" onsubmit="return confirm('Generate ulang pola jadwal seimbang untuk Minggu Ganjil & Genap? Jadwal manual saat ini akan ditimpa.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-stone-700 transition-all hover:border-amber-300 hover:bg-amber-50 hover:text-amber-900">
                        Generate Pola Seimbang
                    </button>
                </form>
            </div>

            <form action="{{ route('superadmin.master-agent.schedules.update') }}" method="POST">
                @csrf
                <input type="hidden" name="parity" value="{{ $selectedParity }}">

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-stone-100 bg-stone-50/40">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-stone-400">Agent</th>
                                @foreach(\App\Models\AgentSchedule::OPERATIONAL_DAYS as $dayNumber => $dayName)
                                    <th class="px-4 py-4 text-center">
                                        <p class="text-xs font-bold uppercase tracking-widest text-stone-600">{{ $dayName }}</p>
                                        <p class="mt-1 text-[11px] font-semibold text-stone-400" id="day-summary-{{ $dayNumber }}">
                                            Pagi: <span class="text-amber-700" data-count-morning="{{ $dayNumber }}">0</span> · Siang: <span class="text-sky-700" data-count-afternoon="{{ $dayNumber }}">0</span>
                                        </p>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($allAgents as $agentItem)
                                <tr class="hover:bg-stone-50/50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="font-bold text-stone-900">{{ $agentItem->name }}</span>
                                            @if(!$agentItem->is_active)
                                                <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-bold text-stone-500">Non-Aktif</span>
                                            @endif
                                        </div>
                                    </td>
                                    @foreach(\App\Models\AgentSchedule::OPERATIONAL_DAYS as $dayNumber => $dayName)
                                        @php
                                            $currentShift = $scheduleMatrix[$selectedParity][$dayNumber][$agentItem->id] ?? 'off';
                                        @endphp
                                        <td class="px-4 py-3 text-center">
                                            <select
                                                name="shifts[{{ $dayNumber }}][{{ $agentItem->id }}]"
                                                data-shift-day="{{ $dayNumber }}"
                                                onchange="updateScheduleSummary()"
                                                class="w-full min-w-[130px] rounded-xl border-stone-200 bg-stone-50 py-2 px-3 text-xs font-bold text-stone-700 focus:border-amber-500 focus:ring-amber-500"
                                            >
                                                <option value="off" {{ $currentShift === 'off' ? 'selected' : '' }}>Libur</option>
                                                <option value="morning" {{ $currentShift === 'morning' ? 'selected' : '' }}>Pagi (08:00–11:20)</option>
                                                <option value="afternoon" {{ $currentShift === 'afternoon' ? 'selected' : '' }}>Siang (13:00–15:40)</option>
                                            </select>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 border-t border-stone-100 bg-stone-50/50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-medium text-stone-500">
                        Sedang mengedit: <span class="font-bold text-stone-800">{{ \App\Models\AgentSchedule::PARITIES[$selectedParity] }}</span> (ISO Week ke-{{ now()->isoWeek() }} · Parity {{ $currentParity }}).
                    </p>
                    <button type="submit" class="rounded-2xl bg-amber-900 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-amber-900/20 transition-all hover:bg-amber-800 active:scale-95">
                        Simpan Jadwal {{ \App\Models\AgentSchedule::PARITIES[$selectedParity] }}
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- Add Agent Modal --}}
<div id="addAgentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-stone-900/60 transition-opacity" onclick="closeModal('addAgentModal')"></div>
        <div class="relative w-full max-w-lg rounded-[2.5rem] bg-white p-8 shadow-2xl transition-all">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-2xl font-black text-stone-900">Tambah Agent Baru</h3>
                <button onclick="closeModal('addAgentModal')" class="text-stone-400 hover:text-stone-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('superadmin.master-agent.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Nama Lengkap</label>
                    <input type="text" name="name" maxlength="255" required class="w-full rounded-2xl border-stone-200 bg-stone-50 py-3 px-4 text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Contoh: Budi Santoso">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Email Kerja</label>
                    <input type="email" name="email" maxlength="255" required class="w-full rounded-2xl border-stone-200 bg-stone-50 py-3 px-4 text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="budi@lkpp.go.id">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Password</label>
                    <input type="password" name="password" minlength="8" required class="w-full rounded-2xl border-stone-200 bg-stone-50 py-3 px-4 text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Minimal 8 karakter">
                </div>
                <div class="flex items-center gap-3 py-2">
                    <input type="checkbox" name="is_active" id="is_active_add" checked value="1" class="h-5 w-5 rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                    <label for="is_active_add" class="text-sm font-bold text-stone-700">Akun Langsung Aktif</label>
                </div>
                <div class="mt-8 flex gap-3 pt-4">
                    <button type="button" onclick="closeModal('addAgentModal')" class="flex-1 rounded-2xl border border-stone-200 bg-white py-3 text-sm font-bold text-stone-600 hover:bg-stone-50 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-2 rounded-2xl bg-amber-900 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-amber-900/20 hover:bg-amber-800 transition-all active:scale-95">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Agent Modal --}}
<div id="editAgentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-stone-900/60 transition-opacity" onclick="closeModal('editAgentModal')"></div>
        <div class="relative w-full max-w-lg rounded-[2.5rem] bg-white p-8 shadow-2xl transition-all">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-2xl font-black text-stone-900">Perbarui Akun Agent</h3>
                <button onclick="closeModal('editAgentModal')" class="text-stone-400 hover:text-stone-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="editAgentForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Nama Lengkap</label>
                    <input type="text" name="name" maxlength="255" id="edit_name" required class="w-full rounded-2xl border-stone-200 bg-stone-50 py-3 px-4 text-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Email Kerja</label>
                    <input type="email" name="email" maxlength="255" id="edit_email" required class="w-full rounded-2xl border-stone-200 bg-stone-50 py-3 px-4 text-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-500">Ganti Password</label>
                    <p class="mb-2 text-[10px] text-stone-400 italic font-medium">*Kosongkan jika tidak ingin mengubah password</p>
                    <input type="password" name="password" minlength="8" class="w-full rounded-2xl border-stone-200 bg-stone-50 py-3 px-4 text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Min 8 karakter">
                </div>
                <div class="flex items-center gap-3 py-2">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="h-5 w-5 rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                    <label for="edit_is_active" class="text-sm font-bold text-stone-700">Akun Aktif</label>
                </div>
                <div class="mt-8 flex gap-3 pt-4">
                    <button type="button" onclick="closeModal('editAgentModal')" class="flex-1 rounded-2xl border border-stone-200 bg-white py-3 text-sm font-bold text-stone-600 hover:bg-stone-50 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-2 rounded-2xl bg-stone-900 px-8 py-3 text-sm font-bold text-white shadow-lg hover:bg-stone-800 transition-all active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openEditModal(agent) {
        const form = document.getElementById('editAgentForm');
        form.action = @json(route('superadmin.master-agent.update', ['agent' => '__AGENT__']))
                    .replace('__AGENT__', agent.id);
        
        document.getElementById('edit_name').value = agent.name;
        document.getElementById('edit_email').value = agent.email;
        document.getElementById('edit_is_active').checked = agent.is_active;
        
        openModal('editAgentModal');
    }

    function updateScheduleSummary() {
        const days = [1, 2, 3, 4];
        days.forEach((day) => {
            const selects = document.querySelectorAll(`select[data-shift-day="${day}"]`);
            if (!selects.length) {
                return;
            }

            let morning = 0;
            let afternoon = 0;
            selects.forEach((select) => {
                if (select.value === 'morning') morning++;
                if (select.value === 'afternoon') afternoon++;
            });

            const morningEl = document.querySelector(`[data-count-morning="${day}"]`);
            const afternoonEl = document.querySelector(`[data-count-afternoon="${day}"]`);
            if (morningEl) morningEl.textContent = morning;
            if (afternoonEl) afternoonEl.textContent = afternoon;
        });
    }

    document.addEventListener('DOMContentLoaded', updateScheduleSummary);
</script>

@if(session('success'))
    <div id="toast" class="fixed bottom-8 right-8 z-[100] animate-bounce-in">
        <div class="flex items-center gap-3 rounded-2xl bg-stone-900 px-6 py-4 text-white shadow-2xl">
            <div class="rounded-full bg-green-500 p-1">
                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast');
            toast.classList.add('opacity-0', 'translate-y-4');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    </script>
@endif

@endsection
