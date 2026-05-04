@extends('layouts.pimpinan')
@section('container')

<div class="px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-4xl border border-stone-200/80 bg-white/90 p-6 shadow-[0_24px_60px_rgba(120,53,15,0.10)] backdrop-blur-xl sm:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-red-700">Pimpinan Report</p>
                    <h1 class="mt-2 text-3xl font-black text-stone-900 sm:text-4xl">Rekap Layanan Selesai</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-relaxed text-stone-600">
                        Daftar layanan yang telah dituntaskan oleh seluruh agent. Data ditampilkan secara global untuk kebutuhan monitoring pimpinan.
                    </p>
                </div>
                <div class="rounded-[24px] border border-red-100 bg-[linear-gradient(145deg,#fff7ed_0%,#ffffff_100%)] px-5 py-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-700">Total Selesai</p>
                    <p class="mt-2 text-3xl font-black text-stone-900">{{ number_format($completedCount) }}</p>
                </div>
            </div>
        </div>

        <section class="mt-6 rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Daftar Layanan</p>
            <h2 class="mt-2 text-xl font-black text-stone-900">Layanan Selesai Terbaru</h2>

            <form method="GET" action="{{ route('pimpinan.layanan-selesai') }}" class="mt-5 grid gap-3 md:grid-cols-6">
                <input
                    type="text"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Cari kode, nama tamu, agent"
                    class="md:col-span-2 rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10"
                >
                <select name="agent_id" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10">
                    <option value="">Semua Agent</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->id }}" {{ (string) ($filters['agent_id'] ?? '') === (string) $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm outline-none focus:border-red-300 focus:ring-4 focus:ring-red-500/10">
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">Terapkan</button>
                <a href="{{ route('pimpinan.layanan-selesai') }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-50">Reset</a>
            </form>

            <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200">
                <table class="w-full text-left text-sm text-stone-600">
                    <thead class="bg-stone-50 text-xs font-bold uppercase text-stone-700">
                        <tr>
                            <th class="px-4 py-3">Waktu Selesai</th>
                            <th class="px-4 py-3">Kode Reservasi</th>
                            <th class="px-4 py-3">Nama Tamu</th>
                            <th class="px-4 py-3">Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($completedReservations as $reservation)
                            <tr class="border-t border-stone-100 hover:bg-stone-50">
                                <td class="px-4 py-3 whitespace-nowrap">{{ optional($reservation->waktu_selesai_tatap_muka)->format('d M Y, H:i') ?? '-' }}</td>
                                <td class="px-4 py-3 font-bold text-stone-900">{{ $reservation->kode_reservasi }}</td>
                                <td class="px-4 py-3">{{ $reservation->nama_lengkap }}</td>
                                <td class="px-4 py-3">{{ optional($reservation->agent)->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-stone-500">Belum ada layanan yang selesai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $completedReservations->links() }}
            </div>
        </section>
    </div>
</div>

@endsection
