@extends('layouts.main')
@section('container')

<div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(220,38,38,0.20),_transparent_35%),linear-gradient(140deg,#fff8f1_0%,#ffffff_42%,#efe8d8_100%)] pt-28 pb-24">
    <div class="mx-auto w-full max-w-5xl px-4 md:px-6">
        <section class="rounded-[32px] border border-red-100 bg-white/92 p-6 shadow-[0_24px_60px_rgba(120,53,15,0.12)] md:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-red-700">Halaman Publik Agent</p>
            <h1 class="mt-3 text-3xl font-black text-stone-900 md:text-4xl">Konfirmasi Nomor Tiket Reservasi</h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-stone-600">
                Gunakan halaman ini untuk memeriksa validitas nomor tiket (kode reservasi). Tidak perlu login. Jika tiket valid, sistem akan menampilkan detail lengkap jadwal reservasi dan agent yang menangani.
            </p>

            <form method="GET" action="{{ route('agent.konfirmasi-tiket') }}" class="mt-7 rounded-[26px] border border-stone-200 bg-stone-50 p-5 md:p-6">
                <label for="nomor_tiket" class="block text-sm font-bold text-stone-700">Nomor Tiket / Kode Reservasi</label>
                <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                    <input
                        type="text"
                        id="nomor_tiket"
                        name="nomor_tiket"
                        value="{{ $nomorTiket }}"
                        placeholder="Contoh: RES-20260426-ABCD"
                        class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm font-bold uppercase tracking-[0.18em] text-stone-900 outline-none transition focus:border-red-500 focus:ring-4 focus:ring-red-500/10"
                    >
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-6 py-3 text-sm font-bold text-white shadow-[0_10px_24px_rgba(127,29,29,0.22)] transition hover:-translate-y-0.5">
                        Konfirmasi Tiket
                    </button>
                </div>
            </form>

            @if ($nomorTiket !== '' && ! $reservation)
                <div class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 text-amber-900">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700">Tidak Valid</p>
                    <p class="mt-2 text-sm">Nomor tiket tidak ditemukan. Pastikan kode sesuai bukti reservasi.</p>
                </div>
            @endif

            @if ($reservation)
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Tiket Valid</p>
                    <p class="mt-2 text-sm">Data reservasi ditemukan dan siap diproses agent.</p>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-stone-200 bg-white px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Nomor Tiket</p>
                        <p class="mt-1 text-sm font-black uppercase tracking-[0.16em] text-stone-900">{{ $reservation->kode_reservasi }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-white px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Jadwal Reservasi</p>
                        <p class="mt-1 text-sm font-bold text-stone-900">{{ \Carbon\Carbon::parse($reservation->tanggal_jam)->translatedFormat('d F Y, H:i') }} WIB</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-white px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Nama Tamu</p>
                        <p class="mt-1 text-sm font-bold text-stone-900">{{ $reservation->nama_lengkap }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-white px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Agent Terjadwal</p>
                        <p class="mt-1 text-sm font-bold text-stone-900">{{ optional($reservation->agent)->name ?? 'Belum ditentukan' }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-white px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Jenis Layanan</p>
                        <p class="mt-1 text-sm font-bold text-stone-900">{{ $reservation->jenis_layanan }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-white px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Status</p>
                        <p class="mt-1 text-sm font-bold text-stone-900">{{ str_replace('_', ' ', $reservation->status) }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-white px-4 py-4 sm:col-span-2">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Detail Keperluan</p>
                        <p class="mt-1 text-sm leading-relaxed text-stone-700">{{ $reservation->detail_keperluan }}</p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>

@endsection
