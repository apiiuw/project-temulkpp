@extends('layouts.superadmin')
@section('container')

<div class="px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-4xl border border-stone-200/80 bg-white/90 p-6 shadow-[0_24px_60px_rgba(120,53,15,0.10)] backdrop-blur-xl sm:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-red-700">Superadmin Report</p>
                    <h1 class="mt-2 text-3xl font-black text-stone-900 sm:text-4xl">Performa Agent</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-relaxed text-stone-600">
                        Perbandingan performa antar agent berdasarkan volume layanan, penyelesaian, dan layanan berjalan.
                    </p>
                </div>
                <div class="rounded-3xl border border-red-100 bg-[linear-gradient(145deg,#fff7ed_0%,#ffffff_100%)] px-5 py-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-700">Agent Aktif</p>
                    <p class="mt-2 text-3xl font-black text-stone-900">{{ number_format($activeAgents) }}/{{ number_format($totalAgents) }}</p>
                </div>
            </div>
        </div>

        <section class="mt-6 rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Daftar Kinerja</p>
            <h2 class="mt-2 text-xl font-black text-stone-900">Performa Agent Keseluruhan</h2>

            <form method="GET" action="{{ route('superadmin.performa-agent') }}" class="mt-5 grid gap-3 md:grid-cols-6">
                <input
                    type="text"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Cari nama atau email agent"
                    class="md:col-span-2 rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10"
                >
                <input
                    type="number"
                    min="0"
                    name="min_completed"
                    value="{{ $filters['min_completed'] ?? 0 }}"
                    placeholder="Minimal layanan selesai"
                    class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10"
                >
                <label class="inline-flex items-center gap-2 rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-semibold text-stone-700">
                    <input type="checkbox" name="only_active" value="1" {{ !empty($filters['only_active']) ? 'checked' : '' }}>
                    Hanya Agent Aktif
                </label>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">Terapkan</button>
                <a href="{{ route('superadmin.performa-agent') }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-50">Reset</a>
            </form>

            <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200">
                <table class="w-full text-left text-sm text-stone-600">
                    <thead class="bg-stone-50 text-xs font-bold uppercase text-stone-700">
                        <tr>
                            <th class="px-4 py-3">Agent</th>
                            <th class="px-4 py-3">Total Layanan</th>
                            <th class="px-4 py-3">Selesai</th>
                            <th class="px-4 py-3">Berjalan</th>
                            <th class="px-4 py-3">Rasio Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agentPerformance as $agent)
                            @php($completionRate = $agent->total_reservations_count > 0 ? round(($agent->completed_reservations_count / $agent->total_reservations_count) * 100) : 0)
                            <tr class="border-t border-stone-100 hover:bg-stone-50">
                                <td class="px-4 py-3 font-bold text-stone-900">{{ $agent->name }}</td>
                                <td class="px-4 py-3">{{ number_format($agent->total_reservations_count) }}</td>
                                <td class="px-4 py-3">{{ number_format($agent->completed_reservations_count) }}</td>
                                <td class="px-4 py-3">{{ number_format($agent->active_reservations_count) }}</td>
                                <td class="px-4 py-3">{{ $completionRate }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-stone-500">Belum ada data performa agent.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $agentPerformance->links() }}
            </div>
        </section>
    </div>
</div>

@endsection
