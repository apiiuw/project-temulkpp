@extends('layouts.frontdesk')
@section('container')

<div
    id="frontdesk-page"
    data-frontdesk-url="{{ route('front-desk') }}"
    data-check-in-success="{{ session('check_in_success') }}"
    data-check-in-error="{{ session('check_in_error') }}"
    class="relative min-h-screen overflow-hidden"
>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(220,38,38,0.34),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(127,29,29,0.3),_transparent_28%),linear-gradient(135deg,#140f0f_0%,#211616_45%,#0f0f10_100%)]"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 py-8 md:px-6 md:py-10">
        <div class="mb-8 flex flex-col gap-5 rounded-[32px] border border-white/12 bg-white/8 p-5 shadow-[0_24px_60px_rgba(0,0,0,0.28)] backdrop-blur-xl md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <div class="rounded-2xl border border-white/20 bg-white/88 px-4 py-3 shadow-sm">
                    <img src="{{ asset('img/icon/logo/logoBlackRed.png') }}" class="h-10 md:h-12" alt="Logo" />
                </div>
                <div class="text-white">
                    <h1 class="mt-2 text-2xl font-extrabold md:text-3xl">Konfirmasi Kedatangan Tamu</h1>
                    <p class="mt-1 text-sm text-white/72">Scan QR atau masukkan kode reservasi untuk menandai tamu sudah hadir.</p>
                </div>
            </div>
        </div>

        @if (session('check_in_success'))
            <div class="mb-6 rounded-2xl border border-emerald-300/20 bg-emerald-400/10 px-5 py-4 text-emerald-50">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-200">Berhasil</p>
                <p class="mt-2 text-sm leading-relaxed">{{ session('check_in_success') }}</p>
            </div>
        @endif

        @if (session('check_in_error'))
            <div class="mb-6 rounded-2xl border border-red-300/20 bg-red-400/10 px-5 py-4 text-red-50">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-red-200">Konfirmasi Ditolak</p>
                <p class="mt-2 text-sm leading-relaxed">{{ session('check_in_error') }}</p>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="flex h-full flex-col rounded-[30px] border border-white/12 bg-white/8 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-xl">
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-red-200">Pemindai QR</p>
                <h2 class="mt-3 text-2xl font-bold text-white">Scan atau Tempel Kode Reservasi</h2>
                <p class="mt-2 text-sm leading-relaxed text-white/72">
                    Scanner memilih kamera yang tersedia secara otomatis. Jika akses kamera gagal, tempel kode reservasi ke input manual di bawah.
                </p>

                <div class="mt-6 rounded-[28px] border border-white/12 bg-white/6 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
                    <div class="mb-4 flex items-center justify-between gap-3 rounded-2xl border border-white/10 bg-black/15 px-4 py-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Status Pemindai</p>
                            <p class="mt-1 text-sm text-white/72">Arahkan QR ke tengah frame agar proses baca lebih stabil.</p>
                        </div>
                        <div class="hidden rounded-full border border-emerald-300/20 bg-emerald-400/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-200 sm:inline-flex">
                            Live
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-dashed border-white/18 bg-black/20 p-4">
                    <div id="scanner-preview-container" class="relative aspect-square overflow-hidden rounded-[22px]">
                        <div id="scanner-preview" class="absolute inset-0 hidden h-full w-full bg-black/40"></div>
                        <div id="scanner-placeholder" class="absolute inset-0 flex items-center justify-center bg-white/6 px-6 text-center text-sm leading-relaxed text-white/60">
                            Kamera sedang dipersiapkan untuk membaca QR code.
                        </div>
                    </div>
                    <p id="scanner-status" class="mt-3 text-center text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                        Scanner sedang disiapkan…
                    </p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <button type="button" id="start-scanner-button" class="inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-red-500 px-5 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(185,29,29,0.24)] transition-transform duration-300 hover:-translate-y-0.5">
                        Mulai / Ulangi Kamera
                    </button>
                    <button type="button" id="stop-scanner-button" class="inline-flex items-center justify-center rounded-2xl border border-white/18 bg-white/10 px-5 py-3 text-sm font-bold text-white/90 transition-transform duration-300 hover:-translate-y-0.5">
                        Hentikan Scan
                    </button>
                </div>

                <div class="mt-4 rounded-2xl border border-white/12 bg-black/15 px-4 py-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-white/55">Tips Cepat</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/8 bg-white/5 px-4 py-3">
                            <p class="text-sm font-semibold text-white">Posisikan QR sejajar</p>
                            <p class="mt-1 text-xs leading-relaxed text-white/60">Hindari sudut terlalu miring agar kode lebih cepat terbaca.</p>
                        </div>
                        <div class="rounded-2xl border border-white/8 bg-white/5 px-4 py-3">
                            <p class="text-sm font-semibold text-white">Gunakan input manual</p>
                            <p class="mt-1 text-xs leading-relaxed text-white/60">Jika scan terhalang, tempel kode reservasi langsung di form bawah.</p>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('front-desk') }}" id="manual-confirm-form" class="mt-6 rounded-[28px] border border-white/12 bg-white/8 p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Input Manual</p>
                            <p class="mt-1 text-sm leading-relaxed text-white/70">Gunakan opsi ini jika QR tidak bisa dibaca atau scanner eksternal hanya mengirimkan teks.</p>
                        </div>
                    </div>
                    <div>
                        <label for="kode_reservasi" class="block text-sm font-bold text-white">Kode Reservasi</label>
                        <input
                            type="text"
                            id="kode_reservasi"
                            name="kode_reservasi"
                            value="{{ $searchedCode }}"
                            placeholder="Contoh: RES-20260407-ABCD"
                            class="mt-2 w-full rounded-2xl border border-white/14 bg-white/90 px-4 py-3 text-center text-sm font-bold uppercase tracking-[0.22em] text-gray-900 outline-none transition-all duration-300 focus:border-red-300 focus:ring-4 focus:ring-red-500/10" maxlength="17" pattern="RES-[0-9]{8}-[A-Za-z0-9]{4}" title="Gunakan format RES-YYYYMMDD-XXXX."
                        >
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-red-700 transition-transform duration-300 hover:-translate-y-0.5">
                        Cari Reservasi
                    </button>
                </form>
            </section>

            <section class="flex h-full flex-col rounded-[30px] border border-white/12 bg-white/92 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)]">
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-red-700">Detail Kedatangan</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900">Data Reservasi</h2>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                    Setelah kode ditemukan, front desk dapat mengonfirmasi bahwa tamu sudah hadir di lokasi.
                </p>

                @if ($searchedCode !== '' && ! $reservation)
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-amber-700">Tidak Ditemukan</p>
                        <p class="mt-2 text-sm leading-relaxed">Reservasi dengan kode tersebut tidak ditemukan. Periksa hasil scan atau input manualnya.</p>
                    </div>
                @endif

                @if ($reservation)
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Kode Reservasi</p>
                            <p class="mt-1 text-sm font-black tracking-[0.18em] text-gray-900">{{ $reservation->kode_reservasi }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Status</p>
                            @php
                                // Warna pill per status. Tambahkan entri di sini bila ada status baru,
                                // supaya pill tidak pernah kosong (kasus 'completed' dulu tidak tercantum).
                                $statusStyleMap = [
                                    'pending'               => ($reservationTiming['is_late'] ?? false) ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700',
                                    'checked_in_front_desk' => 'bg-emerald-100 text-emerald-700',
                                    'in_progress'           => 'bg-orange-100 text-orange-700',
                                    'completed'             => 'bg-gray-200 text-gray-700',
                                    'expired_front_desk'    => 'bg-amber-100 text-amber-700',
                                ];
                                // Teks pill: 4 status pertama memakai label asli halaman ini,
                                // status lain (completed, in_progress, dst) jatuh ke accessor status_label.
                                $statusText = match (true) {
                                    $reservation->status === 'pending' => ($reservationTiming['is_late'] ?? false) ? 'Terlambat' : 'Menunggu Kedatangan',
                                    $reservation->status === 'checked_in_front_desk' => 'Sudah Dikonfirmasi',
                                    $reservation->status === 'expired_front_desk' => 'Lewat Jadwal',
                                    default => $reservation->status_label,
                                };
                            @endphp
                            <p class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] {{ $statusStyleMap[$reservation->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusText }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Nama Lengkap</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->nama_lengkap }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Jabatan</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->jabatan }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Instansi / Perusahaan</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->asal_pt }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Jenis Layanan</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->jenis_layanan }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Jadwal Reservasi</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($reservation->tanggal_jam)->translatedFormat('d F Y, H:i') }} WIB</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4 sm:col-span-2">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Detail Keperluan</p>
                            <p class="mt-1 text-sm leading-relaxed text-gray-700">{{ $reservation->detail_keperluan }}</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-[26px] border border-red-100 bg-linear-to-br from-red-50 to-white px-5 py-5">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-700">Konfirmasi Front Desk</p>
                        @if ($reservation->status === 'completed')
                            <p class="mt-3 text-sm font-semibold text-gray-700">
                                Layanan reservasi ini sudah selesai. Konfirmasi kedatangan sudah ditutup.
                            </p>
                        @elseif ($reservation->checked_in_at)
                            <p class="mt-3 text-sm font-semibold text-emerald-700">
                                Tamu sudah dikonfirmasi hadir pada {{ \Carbon\Carbon::parse($reservation->checked_in_at)->translatedFormat('d F Y, H:i:s') }} WIB.
                            </p>
                        @elseif ($reservation->status === 'expired_front_desk')
                            <p class="mt-3 text-sm font-semibold text-amber-700">
                                Reservasi ini sudah melewati batas keterlambatan maksimal {{ $lateConfirmationGraceMinutes }} menit sehingga tidak dapat dikonfirmasi.
                            </p>
                        @elseif ($reservationTiming['is_late'] ?? false)
                            <p class="mt-3 text-sm font-semibold text-orange-700">
                                Tamu terlambat hadir. Konfirmasi masih bisa dilakukan dalam maksimal {{ $lateConfirmationGraceMinutes }} menit setelah jadwal.
                            </p>
                            <div
                                id="late-countdown-card"
                                data-grace-deadline="{{ $reservationTiming['grace_deadline_iso'] ?? '' }}"
                                class="mt-4 rounded-2xl border border-orange-200 bg-orange-50 px-4 py-4 text-orange-900"
                            >
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-orange-700">Countdown Keterlambatan</p>
                                <p id="late-countdown-text" class="mt-2 text-lg font-black tracking-[0.08em]">
                                    --
                                </p>
                                <p class="mt-2 text-xs leading-relaxed text-orange-700/80">
                                    Jika countdown habis, tombol konfirmasi otomatis dinonaktifkan.
                                </p>
                            </div>
                            <p id="late-expired-message" class="mt-4 hidden text-sm font-semibold text-amber-700">
                                Batas keterlambatan sudah habis. Muat ulang halaman untuk memperbarui status reservasi.
                            </p>
                        @else
                            <p class="mt-3 text-sm font-semibold text-gray-700">
                                Reservasi ini belum dikonfirmasi hadir oleh front desk.
                            </p>
                        @endif

                        <form method="POST" action="{{ route('front-desk.confirm', $reservation->kode_reservasi) }}" id="frontdesk-confirm-form" class="mt-5">
                            @csrf
                            @method('PATCH')
                            {{-- Status yang sudah lewat konfirmasi front desk: tombol dinonaktifkan
                                 agar konfirmasi tidak mengembalikan status (mis. completed -> checked_in_front_desk) --}}
                            @php($confirmBlocked = in_array($reservation->status, ['checked_in_front_desk', 'expired_front_desk', 'in_progress', 'completed'], true))
                            <button
                                type="submit"
                                id="frontdesk-confirm-button"
                                class="w-full inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-6 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(127,29,29,0.25)] transition-transform duration-300 hover:-translate-y-0.5 {{ $confirmBlocked ? 'opacity-60 pointer-events-none' : '' }}"
                                {{ $confirmBlocked ? 'disabled' : '' }}
                            >
                                {{ $reservation->status === 'checked_in_front_desk' ? 'Sudah Dikonfirmasi Hadir' : '' }}
                                {{ $reservation->status === 'expired_front_desk' ? 'Konfirmasi Sudah Ditutup' : '' }}
                                {{ $reservation->status === 'in_progress' ? 'Sedang Berlangsung' : '' }}
                                {{ $reservation->status === 'completed' ? 'Layanan Sudah Selesai' : '' }}
                                {{ ! $confirmBlocked ? 'Konfirmasi Tamu Sudah Datang' : '' }}
                            </button>
                        </form>

                        <a href="{{ route('front-desk') }}" class="mt-3 w-full inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-6 py-3 text-sm font-bold text-red-700 transition-transform duration-300 hover:-translate-y-0.5">
                            Scan Ulang
                        </a>
                    </div>
                @else
                    <div class="mt-6 flex min-h-72 flex-1 flex-col items-center justify-center rounded-[28px] border border-dashed border-gray-200 bg-gray-50 px-6 text-center text-sm leading-relaxed text-gray-500">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-red-50 text-red-600 shadow-[inset_0_0_0_1px_rgba(239,68,68,0.12)]">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.75 7.75A2.75 2.75 0 0 1 7.5 5h9A2.75 2.75 0 0 1 19.25 7.75v8.5A2.75 2.75 0 0 1 16.5 19h-9a2.75 2.75 0 0 1-2.75-2.75v-8.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9.5h8M8 12h5m-5 2.5h3" />
                            </svg>
                        </div>
                        <p class="mt-5 max-w-md">
                            Belum ada data reservasi yang dipilih. Silakan scan QR atau masukkan kode reservasi untuk melihat detail tamu.
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
    /*
     * Panel detail dibuka lewat /front-desk?kode_reservasi=..., tetapi begitu halaman
     * selesai diproses, query string dibersihkan sehingga refresh (F5) selalu membawa
     * operator kembali ke halaman pindai bersih /front-desk.
     *
     * Pembersihan harus MENUNGGU scanner selesai inisialisasi, karena
     * frontdesk-scanner.js masih membaca window.location.search untuk memutuskan
     * apakah kamera langsung dinyalakan atau tidak. Tanda inisialisasinya: elemen
     * #scanner-status berganti dari teks awalnya.
     */
    (function () {
        function stripReservationCode() {
            try {
                var url = new URL(window.location.href);

                if (!url.searchParams.has('kode_reservasi')) {
                    return;
                }

                url.searchParams.delete('kode_reservasi');

                var query = url.searchParams.toString();
                window.history.replaceState(null, '', url.pathname + (query ? '?' + query : '') + url.hash);
            } catch (error) {
                // biarkan; refresh tetap mengikuti URL apa adanya
            }
        }

        var statusElement = document.getElementById('scanner-status');

        if (!statusElement || typeof MutationObserver === 'undefined') {
            window.addEventListener('load', stripReservationCode);
            return;
        }

        var initialText = statusElement.textContent;
        var finished = false;

        function finish() {
            if (finished) {
                return;
            }

            finished = true;
            observer.disconnect();
            stripReservationCode();
        }

        var observer = new MutationObserver(function () {
            if (statusElement.textContent !== initialText) {
                finish();
            }
        });

        observer.observe(statusElement, { childList: true, characterData: true, subtree: true });

        // Jaring pengaman bila scanner gagal dimuat dan tidak pernah mengubah status.
        setTimeout(finish, 5000);
    })();
</script>
@endpush
