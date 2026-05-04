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

    <div class="relative z-10 w-full max-w-5xl mx-auto px-4 pb-40 pt-36 md:pb-44 md:pt-44">
        <div class="bg-white/85 backdrop-blur-xl border border-white/30 rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-8 md:p-12">
                <div class="flex flex-col items-center mb-10 text-center">
                    <div class="p-3 bg-red-600/10 rounded-2xl mb-4">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-linear-to-r from-gray-900 to-red-700">
                        Atur Ulang Jadwal Reservasi
                    </h1>
                    <p class="mt-2 max-w-2xl text-gray-600 font-medium">
                        Masukkan kode reservasi Anda untuk melihat detail reservasi, mengganti jadwal, mengunduh bukti terbaru, atau membatalkan reservasi.
                    </p>
                </div>

                @if (session('cancel_success'))
                    <div class="mb-8 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-red-800">
                        <p class="text-sm font-bold uppercase tracking-[0.2em]">Reservasi Dibatalkan</p>
                        <p class="mt-2 text-sm leading-relaxed">{{ session('cancel_success') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-8 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-red-800">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('reschedule_success') && $reservation)
                    <div class="mb-10 p-8 rounded-2xl bg-linear-to-br from-emerald-50 to-teal-50 border border-emerald-100 shadow-sm">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 bg-emerald-500/20 text-emerald-500 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-emerald-800">{{ session('reschedule_success') }}</h3>
                            <p class="mt-2 text-emerald-600/80 mb-6">Gunakan kode baru ini sebagai bukti jadwal reservasi terbaru Anda.</p>

                            <div class="bg-white p-6 rounded-2xl shadow-inner border border-emerald-50">
                                @php
                                    $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)
                                        ->margin(1)
                                        ->generate($reservation->kode_reservasi);
                                @endphp
                                <div class="flex justify-center mb-4">
                                    <div class="rounded-2xl bg-white p-3 shadow-sm">
                                        {!! $qrCode !!}
                                    </div>
                                </div>
                                <div class="inline-block px-6 py-2 bg-gray-50 rounded-lg">
                                    <p class="text-lg md:text-2xl font-black tracking-[0.3em] text-gray-800">{{ $reservation->kode_reservasi }}</p>
                                </div>
                            </div>

                            <div class="mt-6 w-full rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-left">
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">Pemberitahuan Penting</p>
                                <p class="mt-2 text-sm leading-relaxed text-amber-900">
                                    Tamu wajib hadir minimal 15 menit sebelum jadwal reservasi terbaru yang telah ditetapkan agar proses verifikasi dan antrean dapat berjalan lancar.
                                </p>
                            </div>

                            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                <a
                                    href="{{ route('reservasi.download', $reservation->kode_reservasi) }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-6 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(127,29,29,0.25)] transition-transform duration-300 hover:-translate-y-0.5"
                                >
                                    Unduh Bukti Reservasi Baru
                                </a>
                                <a
                                    href="{{ route('atur-ulang-jadwal') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-6 py-3 text-sm font-bold text-red-700 transition-transform duration-300 hover:-translate-y-0.5"
                                >
                                    Atur Ulang Kode Lain
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-3xl border border-red-100 bg-white/90 p-6 shadow-sm">
                        <form method="GET" action="{{ route('atur-ulang-jadwal') }}" class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                            <div>
                                <label for="kode_reservasi" class="block mb-2 text-sm font-bold text-gray-700">
                                    Kode Reservasi
                                </label>
                                <input
                                    type="text"
                                    id="kode_reservasi"
                                    name="kode_reservasi"
                                    value="{{ old('kode_reservasi', $searchedCode) }}"
                                    placeholder="Contoh: RES-20260407-ABCD"
                                    class="w-full px-4 py-3 uppercase tracking-[0.18em] bg-white border border-gray-200 rounded-2xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none text-center"
                                >
                                <p class="mt-2 text-xs text-gray-500">Masukkan kode reservasi yang tercantum pada QR code atau file PDF reservasi Anda.</p>
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-600 to-red-800 px-6 py-3 text-sm font-bold text-white shadow-[0_10px_20px_rgba(220,38,38,0.28)] transition-all duration-300 hover:-translate-y-0.5 mb-0 md:mb-6">
                                Konfirmasi
                            </button>
                        </form>
                    </div>

                    @if ($searchedCode !== '' && ! $reservation)
                        <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">Kode Tidak Ditemukan</p>
                            <p class="mt-2 text-sm leading-relaxed">
                                Kami tidak menemukan reservasi dengan kode tersebut. Periksa kembali penulisannya lalu coba lagi.
                            </p>
                        </div>
                    @endif

                    @if ($reservation)
                    @php
                        $currentDate = old('tanggal_jam') ? \Carbon\Carbon::parse(old('tanggal_jam'))->format('Y-m-d') : \Carbon\Carbon::parse($reservation->tanggal_jam)->format('Y-m-d');
                        $currentTime = old('tanggal_jam') ? \Carbon\Carbon::parse(old('tanggal_jam'))->format('H:i') : \Carbon\Carbon::parse($reservation->tanggal_jam)->format('H:i');
                    @endphp

                    <div class="mt-8 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                        <div class="space-y-6">
                            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Data Reservasi Saat Ini</p>
                                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Kode Reservasi</p>
                                        <p class="mt-1 text-sm font-black tracking-[0.18em] text-gray-900">{{ $reservation->kode_reservasi }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Nama Lengkap</p>
                                        <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->nama_lengkap }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Jabatan</p>
                                        <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->jabatan }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Instansi / Perusahaan</p>
                                        <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->asal_pt }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Jenis Layanan</p>
                                        <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->jenis_layanan }}</p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Jadwal Saat Ini</p>
                                        <p class="mt-1 text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($reservation->tanggal_jam)->translatedFormat('d F Y, H:i') }} WIB</p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Detail Keperluan</p>
                                        <p class="mt-1 text-sm leading-relaxed text-gray-700">{{ $reservation->detail_keperluan }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-red-100 bg-red-50/80 p-6 shadow-sm">
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Aksi Reservasi</p>
                                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                    <a
                                        href="{{ route('reservasi.download', $reservation->kode_reservasi) }}"
                                        class="inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-5 py-3 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5"
                                    >
                                        Unduh Bukti Reservasi
                                    </a>
                                    <button
                                        type="button"
                                        id="cancel-reservation-button"
                                        class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-bold text-red-700 transition-all duration-300 hover:-translate-y-0.5"
                                    >
                                        Batalkan Reservasi
                                    </button>
                                </div>
                                <p class="mt-3 text-xs leading-relaxed text-red-900">
                                    Jika jadwal diubah, sistem akan mengganti kode reservasi lama dengan kode baru. Jika dibatalkan, reservasi akan dihapus permanen.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Jadwal Baru</p>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">
                                Pilih tanggal dan jam baru. Slot yang sudah digunakan reservasi lain akan otomatis dinonaktifkan.
                            </p>

                            <form action="{{ route('atur-ulang-jadwal.update', $reservation->kode_reservasi) }}" method="POST" id="reschedule-form" class="mt-6 space-y-6">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label for="tanggal_reschedule" class="block mb-2 text-sm font-bold text-gray-700">
                                        Tanggal Baru
                                    </label>
                                    <input
                                        type="text"
                                        id="tanggal_reschedule"
                                        value="{{ $currentDate }}"
                                        required
                                        autocomplete="off"
                                        placeholder="Pilih tanggal baru"
                                        title="Hanya tersedia hari Senin sampai Kamis."
                                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none"
                                    >
                                    <p class="mt-2 text-xs text-gray-500">Tersedia mulai hari ini pada hari Senin sampai Kamis.</p>
                                </div>

                                <div>
                                    <label for="jam_reschedule" class="block mb-2 text-sm font-bold text-gray-700">
                                        Jam Baru
                                    </label>
                                    <div
                                        id="jam_reschedule"
                                        role="radiogroup"
                                        aria-label="Pilihan jam reservasi baru"
                                        title="Interval jadwal tersedia setiap 40 menit dari 08:00 sampai 16:00 WIB, kecuali saat istirahat 12:00 sampai 13:00 WIB."
                                        class="grid grid-cols-2 gap-3 rounded-2xl border border-gray-200 bg-white p-3 md:grid-cols-3"
                                    >
                                        <div
                                            id="jam_reschedule_empty"
                                            class="col-span-full rounded-xl border border-dashed border-gray-200 bg-white/80 px-4 py-5 text-center text-sm font-medium text-gray-500"
                                        >
                                            Pilih tanggal terlebih dahulu
                                        </div>
                                    </div>
                                    <p id="reschedule-slot-tooltip" class="mt-2 text-xs text-gray-500">
                                        Slot penuh akan otomatis dinonaktifkan. Jam 12:00 sampai 13:00 adalah waktu istirahat.
                                    </p>
                                    <input type="hidden" id="tanggal_jam_baru" name="tanggal_jam" value="{{ old('tanggal_jam', \Carbon\Carbon::parse($reservation->tanggal_jam)->format('Y-m-d H:i:s')) }}">
                                </div>

                                <button type="button" id="open-reschedule-confirmation" class="w-full inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-600 to-red-800 px-6 py-3 text-sm font-bold text-white shadow-[0_10px_20px_rgba(220,38,38,0.28)] transition-all duration-300 hover:-translate-y-0.5">
                                    Simpan Jadwal Baru
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="cancel-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
                        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Konfirmasi Pembatalan</p>
                            <h3 class="mt-3 text-2xl font-bold text-gray-900">Yakin ingin menghapus reservasi ini?</h3>
                            <p class="mt-3 text-sm leading-relaxed text-gray-600">
                                Tindakan ini akan membatalkan reservasi dan data reservasi tidak dapat digunakan kembali.
                            </p>
                            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                <button type="button" id="close-cancel-modal" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700">
                                    Tidak, simpan dulu
                                </button>
                                <form action="{{ route('atur-ulang-jadwal.destroy', $reservation->kode_reservasi) }}" method="POST" class="w-full sm:w-auto">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-5 py-3 text-sm font-bold text-white">
                                        Ya, batalkan reservasi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div id="reschedule-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
                        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Konfirmasi Perubahan</p>
                            <h3 class="mt-3 text-2xl font-bold text-gray-900">Yakin ingin menyimpan jadwal baru?</h3>
                            <p class="mt-3 text-sm leading-relaxed text-gray-600">
                                Sistem akan menghapus reservasi lama, membuat kode reservasi baru, lalu menyimpan jadwal terbaru Anda.
                            </p>
                            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                <button type="button" id="close-reschedule-modal" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700">
                                    Tidak, periksa lagi
                                </button>
                                <button type="button" id="confirm-reschedule-submit" class="inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-5 py-3 text-sm font-bold text-white">
                                    Ya, simpan sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const availableTimes = @json($availableTimes);
        const occupiedSlots = @json($occupiedSlots);
        const dateInput = document.getElementById('tanggal_reschedule');
        const timeSlotContainer = document.getElementById('jam_reschedule');
        const timeEmptyState = document.getElementById('jam_reschedule_empty');
        const hiddenDateTimeInput = document.getElementById('tanggal_jam_baru');
        const tooltip = document.getElementById('reschedule-slot-tooltip');
        const nowString = @json(now()->format('Y-m-d H:i:s'));
        const cancelButton = document.getElementById('cancel-reservation-button');
        const cancelModal = document.getElementById('cancel-modal');
        const closeCancelModal = document.getElementById('close-cancel-modal');
        const rescheduleForm = document.getElementById('reschedule-form');
        const openRescheduleConfirmation = document.getElementById('open-reschedule-confirmation');
        const rescheduleConfirmModal = document.getElementById('reschedule-confirm-modal');
        const closeRescheduleModal = document.getElementById('close-reschedule-modal');
        const confirmRescheduleSubmit = document.getElementById('confirm-reschedule-submit');
        let selectedTime = @json($currentTime ?? '');
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
                    const day = dayElem.dateObj.getDay();
                    const dayString = formatLocalDate(dayElem.dateObj);

                    if (day === 0 || day === 5 || day === 6 || (isTodayClosed && dayString === todayString)) {
                        dayElem.classList.add('flatpickr-disabled');
                    }
                },
                disable: [
                    (date) => {
                        const day = date.getDay();
                        const dateString = formatLocalDate(date);
                        return day === 0 || day === 5 || day === 6 || (isTodayClosed && dateString === todayString);
                    },
                ],
            });
        };

        if (cancelButton && cancelModal && closeCancelModal) {
            cancelButton.addEventListener('click', () => {
                cancelModal.classList.remove('hidden');
                cancelModal.classList.add('flex');
            });

            closeCancelModal.addEventListener('click', () => {
                cancelModal.classList.add('hidden');
                cancelModal.classList.remove('flex');
            });

            cancelModal.addEventListener('click', (event) => {
                if (event.target === cancelModal) {
                    cancelModal.classList.add('hidden');
                    cancelModal.classList.remove('flex');
                }
            });
        }

        if (openRescheduleConfirmation && rescheduleConfirmModal && closeRescheduleModal && confirmRescheduleSubmit && rescheduleForm) {
            openRescheduleConfirmation.addEventListener('click', () => {
                if (!rescheduleForm.reportValidity()) {
                    return;
                }

                rescheduleConfirmModal.classList.remove('hidden');
                rescheduleConfirmModal.classList.add('flex');
            });

            closeRescheduleModal.addEventListener('click', () => {
                rescheduleConfirmModal.classList.add('hidden');
                rescheduleConfirmModal.classList.remove('flex');
            });

            confirmRescheduleSubmit.addEventListener('click', () => {
                rescheduleForm.submit();
            });

            rescheduleConfirmModal.addEventListener('click', (event) => {
                if (event.target === rescheduleConfirmModal) {
                    rescheduleConfirmModal.classList.add('hidden');
                    rescheduleConfirmModal.classList.remove('flex');
                }
            });
        }

        if (!dateInput || !timeSlotContainer || !hiddenDateTimeInput || !tooltip) {
            return;
        }

        enableWeekdayPicker(dateInput);

        const isAllowedDay = (dateString) => {
            if (!dateString) return false;
            const [year, month, day] = dateString.split('-').map(Number);
            const date = new Date(year, month - 1, day);
            const dayOfWeek = date.getDay();

            return dayOfWeek >= 1 && dayOfWeek <= 4;
        };

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
                tooltip.textContent = 'Pilih tanggal terlebih dahulu untuk melihat slot jam yang tersedia.';
                syncHiddenDateTime();
                return;
            }

            if (!isAllowedDay(selectedDate)) {
                selectedTime = '';
                timeSlotContainer.setAttribute('aria-disabled', 'true');
                renderEmptyState('Pilih hari Senin sampai Kamis');
                tooltip.textContent = 'Tanggal yang dipilih berada di luar jadwal layanan. Silakan pilih hari Senin sampai Kamis.';
                dateInput.setCustomValidity('Penjadwalan ulang hanya tersedia pada hari Senin sampai Kamis.');
                syncHiddenDateTime();
                return;
            }

            if (selectedDate < todayString) {
                selectedTime = '';
                timeSlotContainer.setAttribute('aria-disabled', 'true');
                renderEmptyState('Tanggal tidak tersedia');
                tooltip.textContent = 'Tanggal baru tidak boleh lebih awal dari hari ini.';
                dateInput.setCustomValidity('Tanggal baru hanya bisa dipilih mulai hari ini.');
                syncHiddenDateTime();
                return;
            }

            if (selectedDate === todayString && isTodayClosed) {
                selectedTime = '';
                timeSlotContainer.setAttribute('aria-disabled', 'true');
                renderEmptyState('Slot hari ini sudah berakhir');
                tooltip.textContent = 'Slot penjadwalan ulang untuk hari ini sudah berakhir. Silakan pilih hari berikutnya.';
                dateInput.setCustomValidity('Tanggal hari ini sudah tidak tersedia karena seluruh slot penjadwalan ulang telah berakhir.');
                syncHiddenDateTime();
                return;
            }

            dateInput.setCustomValidity('');
            timeSlotContainer.setAttribute('aria-disabled', 'false');
            tooltip.textContent = selectedDate === todayString
                ? `Untuk hari ini, hanya jam setelah ${currentTimeString} WIB yang masih bisa dipilih.`
                : 'Slot yang sudah digunakan reservasi lain akan otomatis nonaktif. Klik box jam yang masih tersedia.';

            renderEmptyState('Pilih jam baru');
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
                tooltip.textContent = selectedDate === todayString
                    ? `Tidak ada slot tersisa untuk hari ini setelah jam ${currentTimeString} WIB.`
                    : 'Tidak ada slot tersedia untuk tanggal yang dipilih.';
                syncHiddenDateTime();
            }
        };

        dateInput.addEventListener('change', () => {
            selectedTime = '';
            populateTimeOptions();
        });

        timeSlotContainer.setAttribute('aria-disabled', 'true');
        populateTimeOptions();
    });
</script>
@endpush
@endsection
