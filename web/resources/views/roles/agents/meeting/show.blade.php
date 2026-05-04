@extends('layouts.agent')
@section('container')

<div class="px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-5xl">
        <div class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_24px_60px_rgba(28,25,23,0.08)] sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-red-600">Fitur Agent</p>
            <h1 class="mt-3 text-3xl font-black text-stone-900">Sesi Pelayanan Berlangsung</h1>
            <p class="mt-2 text-sm leading-relaxed text-stone-600">
                Tamu: <span class="font-bold text-stone-900">{{ $reservation->nama_lengkap }}</span>
                • Instansi: <span class="font-bold text-stone-900">{{ $reservation->asal_pt }}</span>
                • Nomor tiket: <span class="font-bold text-stone-900">{{ $reservation->kode_reservasi }}</span>
            </p>

            @if ($tamuLampiranUrl)
                <div class="mt-4 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-blue-700">Dokumen Tamu</p>
                            <p class="mt-1 text-sm text-blue-900">File lampiran tamu tersedia untuk referensi</p>
                        </div>
                        <a href="{{ $tamuLampiranUrl }}" target="_blank" rel="noopener" class="whitespace-nowrap rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-blue-700">Lihat Lampiran</a>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-900">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-700">Validasi Gagal</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-5 text-center">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-700">Countdown Sesi (Durasi Efektif)</p>
                    <p id="countdown" class="mt-2 text-5xl font-black tabular-nums text-red-700 sm:text-6xl">{{ $sessionDurationLabel }}</p>
                    <p class="mt-2 text-xs text-red-700/80">Durasi wawancara = 40 menit dikurangi keterlambatan check-in tamu.</p>
                </div>

                <div class="rounded-2xl border border-stone-200 bg-stone-50 px-5 py-5 text-center">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-500">Stopwatch Pelayanan</p>
                    <p id="stopwatch" class="mt-2 text-5xl font-black tabular-nums text-stone-900 sm:text-6xl">00:00</p>
                    <p class="mt-2 text-xs text-stone-500">Memantau durasi yang sudah berjalan selama pelayanan.</p>
                </div>
            </div>

            <form id="meeting-form" action="{{ route('agent.tatap-muka.end', $reservation->kode_reservasi) }}" method="POST" enctype="multipart/form-data" class="mt-6 rounded-2xl border border-stone-200 bg-stone-50 p-5 sm:p-6">
                @csrf
                <p class="text-sm font-bold text-stone-800">Catatan Pelayanan (Wajib)</p>
                <p class="mt-1 text-xs text-stone-500">Sesi tidak bisa diakhiri jika catatan belum diisi.</p>

                <div class="mt-4">
                    <label for="catatan" class="block text-xs font-semibold text-stone-600">Ringkasan Penanganan</label>
                    <textarea id="catatan" name="catatan" required rows="5" class="mt-2 w-full rounded-xl border border-stone-200 p-3 text-sm outline-none transition focus:border-red-500 focus:ring-4 focus:ring-red-500/10" placeholder="Tuliskan hasil pelayanan, keputusan, dan tindak lanjut.">{{ old('catatan') }}</textarea>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-semibold text-stone-600">File Lampiran (Opsional)</label>
                    <input type="file" name="file_catatan" class="mt-2 w-full cursor-pointer text-sm text-stone-500 file:mr-4 file:rounded-full file:border-0 file:bg-red-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-red-700 hover:file:bg-red-100">
                </div>

                <button type="submit" id="end-session-btn" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-stone-900 px-6 py-3 text-sm font-bold text-white opacity-50 transition" disabled>
                    Upload Hasil Pelayanan
                </button>
                <p id="helper-text" class="mt-3 text-center text-xs text-stone-500">Isi catatan terlebih dahulu untuk mengaktifkan tombol upload.</p>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const endTimeStr = @json($endTime);
        const startTimeStr = @json($startTime);
        const countdownEl = document.getElementById('countdown');
        const stopwatchEl = document.getElementById('stopwatch');
        const catatanEl = document.getElementById('catatan');
        const endSessionBtn = document.getElementById('end-session-btn');
        const helperText = document.getElementById('helper-text');
        const meetingForm = document.getElementById('meeting-form');

        const endTime = new Date(endTimeStr).getTime();
        const startTime = new Date(startTimeStr).getTime();

        const toClock = (seconds) => {
            const safeSeconds = Math.max(0, seconds);
            const minutes = Math.floor(safeSeconds / 60);
            const secs = safeSeconds % 60;

            return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        };

        const syncButtonState = () => {
            const hasNote = catatanEl.value.trim().length > 0;

            endSessionBtn.disabled = !hasNote;
            endSessionBtn.classList.toggle('opacity-50', !hasNote);
            endSessionBtn.classList.toggle('cursor-not-allowed', !hasNote);
            endSessionBtn.classList.toggle('bg-stone-900', !hasNote);
            endSessionBtn.classList.toggle('bg-red-600', hasNote);
            endSessionBtn.classList.toggle('hover:bg-red-700', hasNote);

            helperText.textContent = hasNote
                ? 'Catatan sudah diisi. Anda bisa menyelesaikan sesi kapan pun setelah pelayanan selesai.'
                : 'Isi catatan terlebih dahulu untuk mengaktifkan tombol upload.';
        };

        const renderClock = () => {
            const now = Date.now();
            const remainingSeconds = Math.max(0, Math.floor((endTime - now) / 1000));
            const elapsedSeconds = Math.max(0, Math.floor((now - startTime) / 1000));

            countdownEl.textContent = toClock(remainingSeconds);
            stopwatchEl.textContent = toClock(elapsedSeconds);

            if (remainingSeconds === 0) {
                countdownEl.classList.add('text-stone-500');
                helperText.textContent = catatanEl.value.trim().length > 0
                    ? 'Countdown selesai. Silakan upload catatan pelayanan.'
                    : 'Countdown selesai. Isi catatan untuk menyelesaikan sesi.';
            }
        };

        catatanEl.addEventListener('input', syncButtonState);

        meetingForm.addEventListener('submit', (event) => {
            if (catatanEl.value.trim().length === 0) {
                event.preventDefault();
                helperText.textContent = 'Catatan wajib diisi sebelum sesi bisa diakhiri.';
            }
        });

        syncButtonState();
        renderClock();
        setInterval(renderClock, 1000);
    });
</script>
@endpush

@endsection
