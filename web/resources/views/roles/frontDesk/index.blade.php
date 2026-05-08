@extends('layouts.frontdesk')
@section('container')

<div class="relative min-h-screen overflow-hidden">
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
                    Jika perangkat mendukung kamera dan `BarcodeDetector`, front desk bisa melakukan scan QR langsung. Jika tidak, tempel kode hasil scan ke input di bawah.
                </p>

                <div class="mt-6 rounded-[28px] border border-white/12 bg-white/6 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
                    <div class="mb-4 flex items-center justify-between gap-3 rounded-2xl border border-white/10 bg-black/15 px-4 py-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Status Scanner</p>
                            <p class="mt-1 text-sm text-white/72">Arahkan QR ke tengah frame agar proses baca lebih stabil.</p>
                        </div>
                        <div class="hidden rounded-full border border-emerald-300/20 bg-emerald-400/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-200 sm:inline-flex">
                            Live
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-dashed border-white/18 bg-black/20 p-4">
                    <div id="scanner-preview-container" class="overflow-hidden rounded-[22px]">
                        <div id="scanner-preview" class="h-72 w-full"></div>
                        <div id="scanner-placeholder" class="flex h-72 items-center justify-center bg-white/6 text-center text-sm leading-relaxed text-white/60">
                            Kamera sedang dipersiapkan untuk membaca QR code.
                        </div>
                    </div>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <button type="button" id="start-scanner-button" class="inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-red-500 px-5 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(185,28,28,0.24)] transition-transform duration-300 hover:-translate-y-0.5">
                        Aktifkan Ulang Kamera
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
                            class="mt-2 w-full rounded-2xl border border-white/14 bg-white/90 px-4 py-3 text-center text-sm font-bold uppercase tracking-[0.22em] text-gray-900 outline-none transition-all duration-300 focus:border-red-300 focus:ring-4 focus:ring-red-500/10"
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
                            <p class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em]
                                {{ $reservation->status === 'checked_in_front_desk' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $reservation->status === 'expired_front_desk' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $reservation->status === 'pending' && ($reservationTiming['is_late'] ?? false) ? 'bg-orange-100 text-orange-700' : '' }}
                                {{ $reservation->status === 'pending' && ! ($reservationTiming['is_late'] ?? false) ? 'bg-red-100 text-red-700' : '' }}">
                                {{ $reservation->status === 'checked_in_front_desk' ? 'Sudah Dikonfirmasi' : '' }}
                                {{ $reservation->status === 'expired_front_desk' ? 'Lewat Jadwal' : '' }}
                                {{ $reservation->status === 'pending' && ($reservationTiming['is_late'] ?? false) ? 'Terlambat' : '' }}
                                {{ $reservation->status === 'pending' && ! ($reservationTiming['is_late'] ?? false) ? 'Menunggu Kedatangan' : '' }}
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
                        @if ($reservation->checked_in_at)
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
                            <button
                                type="submit"
                                id="frontdesk-confirm-button"
                                class="w-full inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-6 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(127,29,29,0.25)] transition-transform duration-300 hover:-translate-y-0.5 {{ in_array($reservation->status, ['checked_in_front_desk', 'expired_front_desk'], true) ? 'opacity-60 pointer-events-none' : '' }}"
                                {{ in_array($reservation->status, ['checked_in_front_desk', 'expired_front_desk'], true) ? 'disabled' : '' }}
                            >
                                {{ $reservation->status === 'checked_in_front_desk' ? 'Sudah Dikonfirmasi Hadir' : '' }}
                                {{ $reservation->status === 'expired_front_desk' ? 'Konfirmasi Sudah Ditutup' : '' }}
                                {{ ! in_array($reservation->status, ['checked_in_front_desk', 'expired_front_desk'], true) ? 'Konfirmasi Tamu Sudah Datang' : '' }}
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

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const successMessage = @json(session('check_in_success'));
        const errorMessage = @json(session('check_in_error'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const codeInput = document.getElementById('kode_reservasi');
        const manualConfirmForm = document.getElementById('manual-confirm-form');
        const frontdeskConfirmForm = document.getElementById('frontdesk-confirm-form');
        const frontdeskConfirmButton = document.getElementById('frontdesk-confirm-button');
        const startButton = document.getElementById('start-scanner-button');
        const stopButton = document.getElementById('stop-scanner-button');
        const video = document.getElementById('scanner-preview');
        const placeholder = document.getElementById('scanner-placeholder');
        const lateCountdownCard = document.getElementById('late-countdown-card');
        const lateCountdownText = document.getElementById('late-countdown-text');
        const lateExpiredMessage = document.getElementById('late-expired-message');

        let html5QrCode = null;
        let isScanningResultHandled = false;
        let lateCountdownInterval = null;

        const disableFrontdeskConfirmation = () => {
            if (!frontdeskConfirmButton) {
                return;
            }

            frontdeskConfirmButton.disabled = true;
            frontdeskConfirmButton.classList.add('opacity-60', 'pointer-events-none');
            frontdeskConfirmButton.textContent = 'Konfirmasi Sudah Ditutup';
        };

        const formatCountdown = (totalSeconds) => {
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            return [hours, minutes, seconds]
                .map((value) => String(value).padStart(2, '0'))
                .join(':');
        };

        const startLateCountdown = () => {
            if (!lateCountdownCard || !lateCountdownText) {
                return;
            }

            const deadline = lateCountdownCard.dataset.graceDeadline;
            if (!deadline) {
                return;
            }

            const updateCountdown = () => {
                const remainingSeconds = Math.max(
                    0,
                    Math.floor((new Date(deadline).getTime() - Date.now()) / 1000)
                );

                lateCountdownText.textContent = formatCountdown(remainingSeconds);

                if (remainingSeconds > 0) {
                    return;
                }

                if (lateCountdownInterval) {
                    window.clearInterval(lateCountdownInterval);
                    lateCountdownInterval = null;
                }

                disableFrontdeskConfirmation();
                if (lateExpiredMessage) {
                    lateExpiredMessage.classList.remove('hidden');
                }
            };

            updateCountdown();
            lateCountdownInterval = window.setInterval(updateCountdown, 1000);
        };

        const stopScanner = async () => {
            if (html5QrCode && html5QrCode.isScanning) {
                try {
                    await html5QrCode.stop();
                } catch (err) {
                    console.error("Gagal menghentikan scanner:", err);
                }
            }

            if (video) video.classList.add('hidden');
            if (placeholder) placeholder.classList.remove('hidden');
        };

        const loadReservation = (reservationCode) => {
            stopScanner().then(() => {
                window.location.href = `{{ route('front-desk') }}?kode_reservasi=${encodeURIComponent(reservationCode)}`;
            });
        };

        const activateScanner = async () => {
            try {
                // Hentikan scanner yang mungkin masih berjalan
                await stopScanner();

                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode("scanner-preview");
                }

                video.classList.remove('hidden');
                placeholder.classList.add('hidden');
                isScanningResultHandled = false;

                const config = { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };

                await html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText) => {
                        const scannedValue = (decodedText || '').trim();
                        if (scannedValue && !isScanningResultHandled) {
                            isScanningResultHandled = true;
                            codeInput.value = scannedValue.toUpperCase();
                            loadReservation(scannedValue.toUpperCase());
                        }
                    }
                );
            } catch (error) {
                console.error("Scanner Error:", error);
                await stopScanner();
                await Swal.fire({
                    icon: 'warning',
                    title: 'Kamera Tidak Dapat Diakses',
                    text: 'Pastikan Anda memberikan izin kamera dan tidak ada aplikasi lain yang sedang menggunakan kamera.',
                    confirmButtonColor: '#b91c1c',
                });
            }
        };

        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Tamu Berhasil Dikonfirmasi',
                text: successMessage,
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false,
                confirmButtonColor: '#b91c1c',
            }).then(() => {
                window.location.href = `{{ route('front-desk') }}`;
            });
        }

        if (errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Konfirmasi Ditolak',
                text: errorMessage,
                confirmButtonColor: '#b91c1c',
            });
        }

        if (frontdeskConfirmForm) {
            frontdeskConfirmForm.addEventListener('submit', async (event) => {
                if (frontdeskConfirmButton?.disabled) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();

                const result = await Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi Kehadiran?',
                    text: 'Pastikan tamu yang hadir sudah sesuai dengan data reservasi yang tampil.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, konfirmasi',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#b91c1c',
                    cancelButtonColor: '#6b7280',
                });

                if (result.isConfirmed) {
                    frontdeskConfirmForm.submit();
                }
            });
        }

        startLateCountdown();

        if (!startButton || !stopButton || !video || !placeholder || !codeInput || !manualConfirmForm) {
            return;
        }

        stopButton.addEventListener('click', stopScanner);
        startButton.addEventListener('click', activateScanner);
        manualConfirmForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const manualCode = codeInput.value.trim().toUpperCase();
            if (!manualCode) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Kode Reservasi Kosong',
                    text: 'Masukkan kode reservasi terlebih dahulu sebelum melakukan konfirmasi manual.',
                    confirmButtonColor: '#b91c1c',
                });
                return;
            }

            loadReservation(manualCode);
        });

        activateScanner();
    });
</script>
@endpush
@endsection
