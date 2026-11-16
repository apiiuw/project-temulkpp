@extends('layouts.superadmin')
@section('container')
<div class="min-h-full bg-stone-50/50 px-4 py-8 md:px-8">
    <div class="mx-auto max-w-7xl space-y-8">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-red-700">Data Reservasi</p>
            <h1 class="mt-2 text-3xl font-black text-stone-900">Import Data Reservasi</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-stone-500">Unggah CSV, validasi nilai, tinjau hasil, lalu konfirmasi sebelum data masuk ke reservations.</p>
        </div>
        @if($errors->any()) <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $errors->first() }}</div> @endif
        <div class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr]">
            <section class="rounded-[32px] border border-stone-200 bg-white p-7 shadow-sm">
                <h2 class="text-lg font-black text-stone-900">Unggah CSV</h2>
                <p class="mt-2 text-xs leading-relaxed text-stone-500">Header wajib: tanggal, waktu, jenis_layanan_konsultasi, status, agent. Maksimal 10 MB.</p>
                <form action="{{ route('superadmin.reservation-import.validate') }}" method="POST" enctype="multipart/form-data" class="mt-7 space-y-5">
                    @csrf
                    <label class="flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-amber-200 bg-amber-50/40 p-6 text-center transition hover:border-amber-500 hover:bg-amber-50">
                        <svg class="h-10 w-10 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 16V4m0 0L8 8m4-4 4 4M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/></svg>
                        <span class="mt-3 text-sm font-black text-stone-800">Pilih file CSV reservasi</span>
                        <span class="mt-1 text-xs text-stone-500">Validasi berjalan sebelum import</span>
                        <input type="file" name="file" accept=".csv,.txt" required class="sr-only">
                    </label>
                    <button class="w-full rounded-2xl bg-stone-900 px-6 py-4 text-sm font-black text-white transition hover:bg-stone-700">Validasi Berkas</button>
                </form>
                <div class="mt-7 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-xs leading-relaxed text-amber-900"><strong>Aturan:</strong> error memblokir import. Warning boleh dilanjutkan setelah review. Duplikasi tetap diizinkan sesuai proses bisnis.</div>
            </section>
            <section class="rounded-[32px] border border-stone-200 bg-white p-7 shadow-sm">
                <h2 class="text-lg font-black text-stone-900">Riwayat Unggahan</h2>
                <div class="mt-6 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="border-b border-stone-100 text-xs uppercase tracking-wider text-stone-400"><tr><th class="px-3 py-3">File</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Ringkasan</th><th></th></tr></thead><tbody class="divide-y divide-stone-100">@forelse($batches as $batch)<tr><td class="px-3 py-4 font-bold text-stone-800">{{ $batch->filename }}</td><td class="px-3 py-4"><span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold">{{ $batch->status }}</span></td><td class="px-3 py-4 text-xs text-stone-500">{{ $batch->valid_rows }} valid / {{ $batch->invalid_rows }} error / {{ $batch->warning_rows }} warning</td><td class="px-3 py-4 text-right"><a class="font-bold text-red-700 hover:underline" href="{{ route('superadmin.reservation-import.show', $batch) }}">Buka</a></td></tr>@empty<tr><td colspan="4" class="px-3 py-12 text-center text-sm text-stone-400">Belum ada batch import.</td></tr>@endforelse</tbody></table></div>
                <div class="mt-5">{{ $batches->links() }}</div>
            </section>
        </div>
    </div>
</div>
@endsection
