@extends('layouts.superadmin')
@section('container')

<div class="px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-4xl border border-stone-200/80 bg-white/90 p-6 shadow-[0_24px_60px_rgba(120,53,15,0.10)] backdrop-blur-xl sm:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-red-700">Superadmin Report</p>
                    <h1 class="mt-2 text-3xl font-black text-stone-900 sm:text-4xl">Laporan Layanan</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-relaxed text-stone-600">
                        Rekap data layanan lintas status dengan filter lengkap untuk kebutuhan analisis dan pencetakan laporan.
                    </p>
                </div>
                <div class="rounded-3xl border border-red-100 bg-[linear-gradient(145deg,#fff7ed_0%,#ffffff_100%)] px-5 py-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-700">Total Data</p>
                    <p class="mt-2 text-3xl font-black text-stone-900">{{ number_format($summary['total']) }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-4">
            <div class="rounded-3xl border border-stone-200/80 bg-white p-5 shadow-[0_14px_35px_rgba(28,25,23,0.06)]">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-400">Total Data</p>
                <p class="mt-2 text-2xl font-black text-stone-900">{{ number_format($summary['total']) }}</p>
            </div>
            <div class="rounded-3xl border border-stone-200/80 bg-white p-5 shadow-[0_14px_35px_rgba(28,25,23,0.06)]">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-400">Selesai</p>
                <p class="mt-2 text-2xl font-black text-stone-900">{{ number_format($summary['completed']) }}</p>
            </div>
            <div class="rounded-3xl border border-stone-200/80 bg-white p-5 shadow-[0_14px_35px_rgba(28,25,23,0.06)]">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-400">Berjalan</p>
                <p class="mt-2 text-2xl font-black text-stone-900">{{ number_format($summary['ongoing']) }}</p>
            </div>
        </div>

        <section class="mt-6 rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Filter Laporan</p>
            <h2 class="mt-2 text-xl font-black text-stone-900">Filter dan Search</h2>

            <form method="GET" action="{{ route('superadmin.laporan') }}" class="mt-5 grid gap-3 md:grid-cols-8">
                <input
                    type="text"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Cari kode, tamu, layanan, agent"
                    class="md:col-span-2 rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10"
                >

                <select name="agent_id" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10">
                    <option value="">Semua Agent</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->id }}" {{ (string) ($filters['agent_id'] ?? '') === (string) $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10">
                    <option value="">Semua Status</option>
                    @foreach (['pending','checked_in_front_desk','in_progress','completed','expired_front_desk'] as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>

                <select name="jenis_layanan" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10">
                    <option value="">Semua Layanan</option>
                    @foreach ($serviceTypes as $serviceType)
                        <option value="{{ $serviceType }}" {{ ($filters['jenis_layanan'] ?? '') === $serviceType ? 'selected' : '' }}>{{ $serviceType }}</option>
                    @endforeach
                </select>

                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10">

                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">Terapkan</button>
                <a href="{{ route('superadmin.laporan') }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-50">Reset</a>

                @php($printQuery = request()->except('page'))
                <a
                    href="{{ route('superadmin.laporan.cetak', $printQuery) }}"
                    rel="noopener"
                    class="whitespace-nowrap inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-6 py-2.5 text-sm font-bold text-red-700 hover:bg-red-100"
                >
                    Cetak Laporan
                </a>
            </form>

            <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200">
                <table class="w-full text-left text-sm text-stone-600">
                    <thead class="bg-stone-50 text-xs font-bold uppercase text-stone-700">
                        <tr>
                            <th class="px-4 py-3">Jadwal</th>
                            <th class="px-4 py-3">Kode Reservasi</th>
                            <th class="px-4 py-3">Nama Tamu</th>
                            <th class="px-4 py-3">Layanan</th>
                            <th class="px-4 py-3">Agent</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportRows as $row)
                            <tr class="border-t border-stone-100 hover:bg-stone-50">
                                <td class="px-4 py-3 whitespace-nowrap">{{ optional($row->tanggal_jam)->format('d M Y, H:i') ?? '-' }}</td>
                                <td class="px-4 py-3 font-bold text-stone-900">{{ $row->kode_reservasi }}</td>
                                <td class="px-4 py-3">{{ $row->nama_lengkap }}</td>
                                <td class="px-4 py-3">{{ $row->jenis_layanan }}</td>
                                <td class="px-4 py-3">{{ optional($row->agent)->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold uppercase text-stone-700">{{ str_replace('_', ' ', $row->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-stone-500">Tidak ada data untuk filter yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $reportRows->links() }}
            </div>
        </section>
    </div>
</div>

@endsection
