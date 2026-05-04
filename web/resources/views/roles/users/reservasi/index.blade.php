@extends('layouts.main')
@section('container')

<div class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 w-full h-full overflow-hidden z-0">
        <video
            autoplay
            muted
            loop
            playsinline
            class="absolute top-1/2 left-1/2 min-w-full min-h-full -translate-x-1/2 -translate-y-1/2 object-cover opacity-80"
        >
            <source src="https://player.vimeo.com/external/517604620.sd.mp4?s=d63892cfae8b15099238e888a7c2936316f499c8&profile_id=164&oauth2_token_id=57447761" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="absolute inset-0 bg-linear-to-br from-red-900/60 via-black/40 to-red-900/60 transition-opacity duration-1000"></div>
    </div>

    <div class="relative z-10 w-full max-w-4xl mx-auto px-4 pb-40 pt-36 md:pb-44 md:pt-44">
        <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-3xl shadow-2xl overflow-hidden transform transition-all duration-500 hover:shadow-[0_20px_50px_rgba(0,0,0,0.3)]">
            <div class="p-8 md:p-12">
                <div class="flex flex-col items-center mb-10">
                    <div class="p-3 bg-red-600/10 rounded-2xl mb-4">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-linear-to-r from-gray-900 to-red-700 text-center">
                        Formulir Reservasi
                    </h1>
                    <p class="mt-2 text-gray-600 font-medium text-center">Silakan lengkapi data kunjungan Anda dengan teliti.</p>
                </div>

                @php
                    $oldReservationDate = old('tanggal_jam') ? \Carbon\Carbon::parse(old('tanggal_jam'))->format('Y-m-d') : '';
                    $oldReservationTime = old('tanggal_jam') ? \Carbon\Carbon::parse(old('tanggal_jam'))->format('H:i') : '';
                @endphp

                @if(session('success') && session('kode_reservasi'))
                    <div class="mb-10 p-8 rounded-2xl bg-linear-to-br from-emerald-50 to-teal-50 border border-emerald-100 shadow-sm animate-fade-in-down">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 bg-emerald-500/20 text-emerald-500 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-emerald-800">{{ session('success') }}</h3>
                            <p class="mt-2 text-emerald-600/80 mb-6">Simpan kode ini sebagai bukti reservasi resmi Anda.</p>

                            <div class="bg-white p-6 rounded-2xl shadow-inner border border-emerald-50">
                                @php
                                    $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)
                                        ->margin(1)
                                        ->generate(session('kode_reservasi'));
                                @endphp
                                <div class="flex justify-center mb-4 transform transition-transform duration-500 hover:scale-105">
                                    <div class="rounded-2xl bg-white p-3 shadow-sm">
                                        {!! $qrCode !!}
                                    </div>
                                </div>
                                <div class="inline-block px-6 py-2 bg-gray-50 rounded-lg">
                                    <p class="text-lg md:text-2xl font-black tracking-[0.3em] text-gray-800">{{ session('kode_reservasi') }}</p>
                                </div>
                            </div>

                            <div class="mt-6 w-full rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-left">
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">Pemberitahuan Penting</p>
                                <p class="mt-2 text-sm leading-relaxed text-amber-900">
                                    Tamu wajib hadir minimal 15 menit sebelum jadwal reservasi yang telah ditetapkan agar proses verifikasi dan antrean dapat berjalan lancar.
                                </p>
                            </div>

                            <a
                                href="{{ route('reservasi.download', session('kode_reservasi')) }}"
                                id="download-pdf-button"
                                class="mt-6 inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-6 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(127,29,29,0.25)] transition-transform duration-300 hover:-translate-y-0.5"
                            >
                                Unduh Bukti Reservasi PDF
                            </a>

                            <button
                                type="button"
                                id="back-button"
                                disabled
                                title="Unduh bukti reservasi PDF terlebih dahulu sebelum kembali."
                                class="mt-4 inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-6 py-3 text-sm font-bold text-gray-400 shadow-sm transition-all duration-300 cursor-not-allowed"
                            >
                                Kembali
                            </button>

                            <p id="back-helper-text" class="mt-3 text-xs text-gray-500">
                                Tombol kembali akan aktif setelah bukti reservasi PDF berhasil Anda unduh.
                            </p>
                        </div>
                    </div>
                @else
                    @if ($errors->any())
                        <div class="mb-8 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 animate-shake">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-sm font-medium">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('reservasi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 md:space-y-7">
                        @csrf

                        <section class="overflow-hidden rounded-[28px] border border-red-100 bg-linear-to-br from-red-50/90 via-white to-red-100/70 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                            <div class="border-b border-red-100/80 px-6 py-5 md:px-7">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-4">
                                    <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-600 text-sm font-black text-white shadow-[0_10px_24px_rgba(220,38,38,0.22)]">01</div>
                                    <div class="min-w-0">
                                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-700 sm:text-sm">Jadwal Kunjungan</p>
                                            <h2 class="mt-1 text-xl font-black leading-tight text-gray-900 sm:text-2xl">Pilih layanan dan waktu kedatangan</h2>
                                            <p class="mt-2 max-w-lg text-sm leading-relaxed text-gray-600">
                                                Isi bagian ini terlebih dahulu agar sistem bisa menampilkan slot jam yang sesuai.
                                            </p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 px-6 py-6 md:px-7 md:py-7">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="group">
                                    <label for="jenis_layanan" class="block mb-2 text-sm font-bold text-gray-700 group-focus-within:text-red-600 transition-colors">
                                        Jenis Layanan
                                    </label>
                                    <select id="jenis_layanan" name="jenis_layanan" required
                                        class="w-full px-4 py-3 bg-white/80 border border-gray-200 rounded-xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none appearance-none">
                                        <option value="" disabled {{ old('jenis_layanan') ? '' : 'selected' }}>Pilih layanan</option>
                                        @foreach($serviceTypes as $type)
                                            <option value="{{ $type->name }}" {{ old('jenis_layanan') == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="group">
                                    <label for="tanggal_reservasi" class="block mb-2 text-sm font-bold text-gray-700 group-focus-within:text-red-600 transition-colors">
                                        Tanggal Reservasi
                                    </label>
                                    <input
                                        type="text"
                                        id="tanggal_reservasi"
                                        value="{{ $oldReservationDate }}"
                                        required
                                        autocomplete="off"
                                        placeholder="Pilih tanggal reservasi"
                                        title="Pilih hari Senin sampai Kamis. Slot yang penuh akan otomatis dinonaktifkan pada pilihan jam."
                                        class="w-full px-4 py-3 bg-white/80 border border-gray-200 rounded-xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none"
                                    >
                                    <p class="mt-2 text-xs text-gray-500" title="Tanggal hanya tersedia mulai hari ini pada hari Senin-Kamis.">
                                        Hanya tersedia mulai hari ini pada Senin-Kamis.
                                    </p>
                                </div>
                            </div>

                            <div class="grid items-start gap-6 md:grid-cols-2">
                                <div class="group">
                                    <label for="jam_reservasi" class="block mb-2 text-sm font-bold text-gray-700 group-focus-within:text-red-600 transition-colors">
                                        Jam Reservasi
                                    </label>
                                    <div
                                        id="jam_reservasi"
                                        role="radiogroup"
                                        aria-label="Pilihan jam reservasi"
                                        title="Jam reservasi tersedia dari 08:00 sampai 16:00 WIB dengan interval 40 menit, kecuali saat istirahat 12:00 sampai 13:00 WIB."
                                        class="grid grid-cols-2 gap-3 rounded-2xl border border-gray-200 bg-white/85 p-3 md:grid-cols-3"
                                    >
                                        <div
                                            id="jam_reservasi_empty"
                                            class="col-span-full rounded-xl border border-dashed border-gray-200 bg-white/80 px-4 py-5 text-center text-sm font-medium text-gray-500"
                                        >
                                            Pilih tanggal terlebih dahulu
                                        </div>
                                    </div>
                                    <p id="slot-tooltip" class="mt-2 text-xs text-gray-500" title="Slot yang sudah dipakai akan otomatis nonaktif di daftar jam.">
                                        Interval reservasi setiap 40 menit, mulai 08:00 sampai 16:00 WIB. Jam 12:00 sampai 13:00 adalah waktu istirahat.
                                    </p>
                                    <input type="hidden" id="tanggal_jam" name="tanggal_jam" value="{{ old('tanggal_jam') }}">
                                </div>

                                <div class="group self-start rounded-2xl border border-red-100 bg-white/90 p-5 shadow-sm md:mt-6" title="Slot yang sudah terisi akan otomatis nonaktif.">
                                    <div class="flex items-start gap-4">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-600 text-white shadow-[0_10px_24px_rgba(220,38,38,0.18)]">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4m0 4h.01M22 12A10 10 0 1112 2a10 10 0 0110 10z"></path>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Panduan Jadwal</p>
                                            <p class="mt-2 text-sm leading-relaxed text-red-950">
                                                Pilih tanggal kerja Senin-Kamis, lalu klik box jam yang masih tersedia.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-4 space-y-3 rounded-2xl border border-red-100/80 bg-red-50/70 p-4">
                                        <div class="flex items-center gap-3 text-sm text-red-900">
                                            <span class="inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-red-500"></span>
                                            <span>Interval layanan setiap 40 menit mulai 08:00 sampai 16:00 WIB.</span>
                                        </div>
                                        <div class="flex items-center gap-3 text-sm text-red-900">
                                            <span class="inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400"></span>
                                            <span>Jam 12:00 sampai 13:00 digunakan sebagai waktu istirahat.</span>
                                        </div>
                                        <div class="flex items-center gap-3 text-sm text-red-900">
                                            <span class="inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-gray-400"></span>
                                            <span>Slot yang penuh atau sudah lewat akan otomatis nonaktif.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </section>

                        <section class="overflow-hidden rounded-[28px] border border-gray-100 bg-white/80 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
                            <div class="border-b border-gray-100 px-6 py-5 md:px-7">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-4">
                                    <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gray-900 text-sm font-black text-white shadow-[0_10px_24px_rgba(15,23,42,0.16)]">02</div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-700 sm:text-sm">Data Diri</p>
                                        <h2 class="mt-1 text-xl font-black leading-tight text-gray-900 sm:text-2xl">Lengkapi identitas dan kebutuhan Anda</h2>
                                        <p class="mt-2 max-w-lg text-sm leading-relaxed text-gray-600">
                                            Data ini dipakai untuk verifikasi saat kunjungan dan mempermudah tim memahami keperluan Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 px-6 py-6 md:px-7 md:py-7">
                            <div class="grid gap-6 gap-x-6 md:grid-cols-2">
                                <div class="group">
                                    <label for="nama_lengkap" class="block mb-2 text-sm font-bold text-gray-700 group-focus-within:text-red-600 transition-colors">
                                        Nama Lengkap
                                    </label>
                                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                                        class="w-full px-4 py-3 bg-white/80 border border-gray-200 rounded-xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none"
                                        placeholder="Contoh: Vincent Elvian" maxlength="50">
                                </div>

                                <div class="group">
                                    <label for="jabatan" class="block mb-2 text-sm font-bold text-gray-700 group-focus-within:text-red-600 transition-colors">
                                        Jabatan
                                    </label>
                                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan') }}" required
                                        class="w-full px-4 py-3 bg-white/80 border border-gray-200 rounded-xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none"
                                        placeholder="Contoh: Staf Pengadaan" maxlength="100">
                                </div>

                                <div class="group md:col-span-2">
                                    <label for="asal_pt" class="block mb-2 text-sm font-bold text-gray-700 group-focus-within:text-red-600 transition-colors">
                                        Instansi/Perusahaan
                                    </label>
                                    <input type="text" id="asal_pt" name="asal_pt" value="{{ old('asal_pt') }}" required
                                        class="w-full px-4 py-3 bg-white/80 border border-gray-200 rounded-xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none"
                                        placeholder="Contoh: PT Bangun Negeri" maxlength="100">
                                </div>
                            </div>

                            <div class="group">
                                <label for="detail_keperluan" class="block mb-2 text-sm font-bold text-gray-700 group-focus-within:text-red-600 transition-colors">
                                    Detail Keperluan
                                </label>
                                <textarea id="detail_keperluan" name="detail_keperluan" rows="4" required
                                    class="w-full px-4 py-3 bg-white/80 border border-gray-200 rounded-xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none resize-none"
                                    placeholder="Jelaskan secara singkat kebutuhan, tujuan kunjungan, atau hal yang ingin dikonsultasikan...">{{ old('detail_keperluan') }}</textarea>
                            </div>

                            <div class="group rounded-2xl border border-gray-100 bg-linear-to-br from-white via-red-50/35 to-white p-5 shadow-sm">
                                <label class="block mb-3 text-sm font-bold text-gray-700" for="lampiran">
                                    Unggah Lampiran <span class="ml-1 text-xs font-normal text-gray-400">(Optional, Max 5MB: PDF/JPG/PNG)</span>
                                </label>
                                <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 transition-all duration-300 hover:border-red-200 hover:shadow-[0_16px_30px_rgba(127,29,29,0.08)]">
                                    <input
                                        name="lampiran"
                                        id="lampiran"
                                        type="file"
                                        accept=".pdf,.jpeg,.jpg,.png"
                                        class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                    >
                                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-600/10 text-red-700">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">Tarik file ke area ini atau klik untuk memilih</p>
                                                <p class="mt-1 text-sm leading-relaxed text-gray-500">
                                                    Dokumen pendukung akan membantu proses verifikasi kebutuhan Anda.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="inline-block whitespace-nowrap items-center justify-center rounded-full bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-[0_10px_20px_rgba(220,38,38,0.2)]">
                                            Pilih File
                                        </div>
                                    </div>
                                    <div class="mt-4 flex flex-col gap-2 text-xs text-gray-500 md:flex-row md:items-center md:justify-between">
                                        <p>Format yang didukung: PDF, JPG, JPEG, PNG.</p>
                                        <p>Maksimal ukuran file 5MB.</p>
                                    </div>
                                    <div id="lampiran_filename" class="mt-4 rounded-xl border border-dashed border-gray-200 bg-white/80 px-4 py-3 text-sm font-medium text-gray-500">
                                        Belum ada file yang dipilih
                                    </div>
                                </div>
                            </div>
                            </div>
                        </section>

                        <div class="rounded-[28px] border border-gray-100 bg-white/85 px-6 py-5 shadow-sm md:px-7">
                            <div class="flex flex-col gap-4 md:items-center md:justify-between">
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-2xl bg-linear-to-r from-red-600 to-red-800 px-6 py-4 text-sm font-bold text-white shadow-[0_10px_20px_rgba(220,38,38,0.28)] transition-all duration-300 hover:translate-y-[-2px] hover:shadow-[0_15px_30px_rgba(185,28,28,0.36)] md:w-auto md:min-w-[260px]">
                                    Amankan Antrean Sekarang
                                </button>
                                <p class="max-w-2xl text-xs leading-relaxed text-gray-400 text-center">
                                    Dengan mengirimkan formulir ini, Anda menyetujui ketentuan layanan dan privasi TemuLKPP.
                                    Pastikan data yang Anda masukkan sudah benar sebelum menekan tombol submit.
                                </p>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="back-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Konfirmasi Kembali</p>
        <h3 class="mt-3 text-2xl font-bold text-gray-900">Yakin ingin kembali?</h3>
        <p class="mt-3 text-sm leading-relaxed text-gray-600">
            Jika kembali, tampilan sukses reservasi ini akan ditutup dan Anda akan kembali ke halaman formulir reservasi.
        </p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <button type="button" id="cancel-back-button" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700">
                Tidak, tetap di sini
            </button>
            <a href="{{ route('reservasi') }}" class="inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-5 py-3 text-sm font-bold text-white">
                Ya, kembali
            </a>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    @keyframes fade-in-down {
        0% { opacity: 0; transform: translateY(-10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    .animate-fade-in-down { animation: fade-in-down 0.5s ease-out; }
    .animate-shake { animation: shake 0.3s ease-in-out; }
</style>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const successShown = @json(session()->has('success'));
        const availableTimes = @json($availableTimes);
        const occupiedSlots = @json($occupiedSlots);
        const dateInput = document.getElementById('tanggal_reservasi');
        const timeSlotContainer = document.getElementById('jam_reservasi');
        const timeEmptyState = document.getElementById('jam_reservasi_empty');
        const hiddenDateTimeInput = document.getElementById('tanggal_jam');
        const slotTooltip = document.getElementById('slot-tooltip');
        const reservationForm = hiddenDateTimeInput?.closest('form');
        const fileInput = document.getElementById('lampiran');
        const fileNameLabel = document.getElementById('lampiran_filename');
        const fileInputCard = fileInput?.parentElement;
        const nowString = @json(now()->format('Y-m-d H:i:s'));
        const downloadButton = document.getElementById('download-pdf-button');
        const backButton = document.getElementById('back-button');
        const backHelperText = document.getElementById('back-helper-text');
        const modal = document.getElementById('back-confirm-modal');
        const cancelBackButton = document.getElementById('cancel-back-button');
        const defaultSelectLabel = 'Pilih jam reservasi';
        let selectedTime = @json($oldReservationTime);
        const todayString = nowString.slice(0, 10);
        const currentTimeString = nowString.slice(11, 16);
        const lastReservableTime = '15:40';
        const isTodayClosed = currentTimeString >= lastReservableTime;
        const formatLocalDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        };
        const enableWeekdayPicker = (input) => {
            if (!input || typeof window.flatpickr !== 'function') {
                return;
            }

            window.flatpickr(input, {
                dateFormat: 'Y-m-d',
                allowInput: false,
                disableMobile: true,
                minDate: todayString,
                onDayCreate: (_, __, ___, dayElem) => {
                    let day = dayElem.dateObj.getDay();
                    if (day === 0) day = 7; // ISO
                    const dayString = formatLocalDate(dayElem.dateObj);

                    if (!allowedDays.includes(day) || (isTodayClosed && dayString === todayString)) {
                        dayElem.classList.add('flatpickr-disabled');
                    }
                },
                disable: [
                    (date) => {
                        let day = date.getDay();
                        if (day === 0) day = 7; // ISO
                        const dateString = formatLocalDate(date);
                        return !allowedDays.includes(day) || (isTodayClosed && dateString === todayString);
                    },
                ],
            });
        };

        if (successShown && downloadButton && backButton && backHelperText && modal && cancelBackButton) {
            const enableBackButton = () => {
                backButton.disabled = false;
                backButton.classList.remove('border-gray-300', 'bg-white', 'text-gray-400', 'cursor-not-allowed');
                backButton.classList.add('border-red-200', 'bg-red-50', 'text-red-700', 'cursor-pointer', 'hover:-translate-y-0.5');
                backButton.removeAttribute('title');
                backHelperText.textContent = 'Bukti reservasi sudah diunduh. Anda sekarang bisa kembali ke halaman formulir.';
            };

            downloadButton.addEventListener('click', () => {
                window.setTimeout(enableBackButton, 500);
            });

            backButton.addEventListener('click', () => {
                if (backButton.disabled) {
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });

            cancelBackButton.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });

            return;
        }

        if (!dateInput || !timeSlotContainer || !hiddenDateTimeInput || !slotTooltip) {
            return;
        }

        const allowedDays = {!! json_encode($allowedDays) !!}.map(Number);
        const isAllowedDay = (dateString) => {
            if (!dateString) return false;
            const [year, month, day] = dateString.split('-').map(Number);
            const date = new Date(year, month - 1, day);
            let dayOfWeek = date.getDay();
            if (dayOfWeek === 0) dayOfWeek = 7; // Convert Sunday 0 to 7
            
            return allowedDays.includes(dayOfWeek);
        };

        enableWeekdayPicker(dateInput);

        const syncHiddenDateTime = () => {
            if (dateInput.value && selectedTime) {
                hiddenDateTimeInput.value = `${dateInput.value} ${selectedTime}:00`;
                return;
            }

            hiddenDateTimeInput.value = '';
        };

        const renderEmptyState = (message) => {
            if (!timeEmptyState) {
                return;
            }

            timeEmptyState.textContent = message;
            timeEmptyState.classList.remove('hidden');
        };

        const clearTimeSlots = () => {
            Array.from(timeSlotContainer.querySelectorAll('[data-time-slot]')).forEach((slot) => slot.remove());
        };

        const createTimeSlot = (time, { disabled = false, reason = '' } = {}) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.timeSlot = time;
            button.dataset.time = time;
            button.disabled = disabled;
            button.setAttribute('role', 'radio');
            button.setAttribute('aria-checked', selectedTime === time ? 'true' : 'false');
            button.title = reason || `${time} WIB`;
            button.className = [
                'rounded-xl border px-3 py-3 text-left transition-all duration-200',
                'focus:outline-none focus:ring-4 focus:ring-red-500/10',
                disabled
                    ? 'cursor-not-allowed border-gray-200 bg-gray-100/80 text-gray-400'
                    : selectedTime === time
                        ? 'border-red-600 bg-red-600 text-white shadow-[0_10px_24px_rgba(220,38,38,0.22)]'
                        : 'border-gray-200 bg-white text-gray-700 hover:-translate-y-0.5 hover:border-red-300 hover:bg-red-50/70',
            ].join(' ');

            const label = document.createElement('div');
            label.className = 'text-sm font-bold';
            label.textContent = `${time} WIB`;

            const status = document.createElement('div');
            status.className = disabled
                ? 'mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400'
                : selectedTime === time
                    ? 'mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-red-100'
                    : 'mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-red-500';
            status.textContent = disabled ? reason : (selectedTime === time ? 'Dipilih' : 'Tersedia');

            button.append(label, status);

            if (!disabled) {
                button.addEventListener('click', () => {
                    selectedTime = time;
                    populateTimeOptions();
                });
            }

            return button;
        };

        const populateTimeOptions = () => {
            const selectedDate = dateInput.value;
            const takenTimes = occupiedSlots[selectedDate] ?? [];
            const previousValue = selectedTime;

            clearTimeSlots();

            if (!selectedDate) {
                selectedTime = '';
                timeSlotContainer.setAttribute('aria-disabled', 'true');
                renderEmptyState('Pilih tanggal terlebih dahulu');
                slotTooltip.textContent = 'Pilih tanggal terlebih dahulu untuk melihat slot jam yang tersedia.';
                syncHiddenDateTime();
                return;
            }

            if (!isAllowedDay(selectedDate)) {
                selectedTime = '';
                timeSlotContainer.setAttribute('aria-disabled', 'true');
                renderEmptyState('Pilih hari Senin sampai Kamis');
                slotTooltip.textContent = 'Tanggal yang dipilih berada di luar jadwal layanan. Silakan pilih hari Senin sampai Kamis.';
                dateInput.setCustomValidity('Reservasi hanya tersedia pada hari Senin sampai Kamis.');
                syncHiddenDateTime();
                return;
            }

            if (selectedDate < todayString) {
                selectedTime = '';
                timeSlotContainer.setAttribute('aria-disabled', 'true');
                renderEmptyState('Tanggal tidak tersedia');
                slotTooltip.textContent = 'Tanggal reservasi tidak boleh lebih awal dari hari ini.';
                dateInput.setCustomValidity('Tanggal reservasi hanya bisa dipilih mulai hari ini.');
                syncHiddenDateTime();
                return;
            }

            if (selectedDate === todayString && isTodayClosed) {
                selectedTime = '';
                timeSlotContainer.setAttribute('aria-disabled', 'true');
                renderEmptyState('Slot hari ini sudah berakhir');
                slotTooltip.textContent = 'Slot reservasi untuk hari ini sudah berakhir. Silakan pilih hari berikutnya.';
                dateInput.setCustomValidity('Tanggal hari ini sudah tidak tersedia karena seluruh slot reservasi telah berakhir.');
                syncHiddenDateTime();
                return;
            }

            dateInput.setCustomValidity('');
            timeSlotContainer.setAttribute('aria-disabled', 'false');
            slotTooltip.textContent = selectedDate === todayString
                ? `Untuk hari ini, hanya jam setelah ${currentTimeString} WIB yang masih bisa dipilih.`
                : 'Slot yang sudah terisi otomatis dinonaktifkan. Klik salah satu box jam yang masih tersedia.';

            renderEmptyState(defaultSelectLabel);
            timeEmptyState?.classList.add('hidden');
            selectedTime = '';

            availableTimes.forEach((time) => {
                let disabled = false;
                let reason = '';

                if (selectedDate === todayString && time <= currentTimeString) {
                    disabled = true;
                    reason = 'Lewat';
                }

                if (takenTimes.includes(time)) {
                    disabled = true;
                    reason = 'Full';
                }

                if (previousValue === time && !disabled) {
                    selectedTime = time;
                }

                timeSlotContainer.appendChild(createTimeSlot(time, { disabled, reason }));
            });

            syncHiddenDateTime();

            const hasAvailableOption = Array.from(timeSlotContainer.querySelectorAll('[data-time-slot]'))
                .some((slot) => !slot.disabled);

            if (!hasAvailableOption) {
                selectedTime = '';
                timeSlotContainer.setAttribute('aria-disabled', 'true');
                renderEmptyState(selectedDate === todayString ? 'Tidak ada slot tersisa hari ini' : 'Tidak ada slot tersedia');
                slotTooltip.textContent = selectedDate === todayString
                    ? `Tidak ada slot tersisa untuk hari ini setelah jam ${currentTimeString} WIB.`
                    : 'Tidak ada slot tersedia untuk tanggal yang dipilih.';
                syncHiddenDateTime();
            }
        };

        dateInput.addEventListener('change', () => {
            selectedTime = '';
            populateTimeOptions();
        });

        reservationForm?.addEventListener('submit', (event) => {
            if (hiddenDateTimeInput.value) {
                return;
            }

            event.preventDefault();
            slotTooltip.textContent = 'Pilih salah satu jam reservasi yang tersedia sebelum mengirim formulir.';
            timeSlotContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        fileInput?.addEventListener('change', () => {
            const selectedFile = fileInput.files?.[0];

            if (!selectedFile || !fileNameLabel || !fileInputCard) {
                if (fileNameLabel) {
                    fileNameLabel.textContent = 'Belum ada file yang dipilih';
                }
                return;
            }

            fileNameLabel.textContent = `File terpilih: ${selectedFile.name}`;
            fileNameLabel.classList.remove('border-dashed', 'border-gray-200', 'bg-white/80', 'text-gray-500');
            fileNameLabel.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
            fileInputCard.classList.remove('border-gray-200');
            fileInputCard.classList.add('border-red-300', 'shadow-[0_16px_30px_rgba(127,29,29,0.12)]');
        });

        timeSlotContainer.setAttribute('aria-disabled', 'true');
        populateTimeOptions();
    });
</script>
@endpush
@endsection
