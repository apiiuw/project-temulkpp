@extends('layouts.agent')
@section('container')

<div class="px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-7xl">
        <section class="rounded-[30px] border border-stone-200 bg-white/92 p-6 shadow-[0_22px_54px_rgba(120,53,15,0.10)] sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.28em] text-red-700">Fitur Agent Detail</p>
            <h1 class="mt-3 text-3xl font-black text-stone-900">Detail Reservasi & Hasil Layanan</h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-stone-600">
                Halaman ini menampilkan data reservasi yang Anda tangani, termasuk catatan hasil layanan, file pendukung, dan durasi tatap muka. Gunakan pencarian dan filter untuk menemukan data yang dibutuhkan lebih cepat.
            </p>
        </section>

        <section class="mt-6 rounded-[28px] border border-stone-200 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-stone-400">Filter Data</p>
                    <h2 class="mt-2 text-xl font-black text-stone-900">Cari hasil layanan</h2>
                </div>
                <a href="{{ route('agent.detail-layanan') }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-50">Reset</a>
            </div>

            <form method="GET" action="{{ route('agent.detail-layanan') }}" class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                <label class="block xl:col-span-2">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Search</span>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Kode, nama, instansi, jabatan, layanan"
                        class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-800 outline-none transition focus:border-red-400"
                    >
                </label>

                <label class="block">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Status</span>
                    <select name="status" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-800 outline-none transition focus:border-red-400">
                        <option value="">Semua Status</option>
                        @foreach (['pending' => 'Pending', 'checked_in_front_desk' => 'Sudah Check-In', 'in_progress' => 'Sedang Berlangsung', 'completed' => 'Selesai', 'expired_front_desk' => 'Kadaluarsa'] as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Dari Tanggal</span>
                    <input
                        type="date"
                        name="date_from"
                        value="{{ $filters['date_from'] ?? '' }}"
                        class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-800 outline-none transition focus:border-red-400"
                    >
                </label>

                <label class="block">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Sampai Tanggal</span>
                    <input
                        type="date"
                        name="date_to"
                        value="{{ $filters['date_to'] ?? '' }}"
                        class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-800 outline-none transition focus:border-red-400"
                    >
                </label>

                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-red-700">Terapkan</button>
                </div>
            </form>
        </section>

        <section class="mt-6 rounded-[28px] border border-stone-200 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Data Layanan</p>
            <h2 class="mt-2 text-xl font-black text-stone-900">Reservasi Yang Ditangani</h2>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full text-left text-sm text-stone-600">
                    <thead class="bg-stone-50 text-xs font-bold uppercase text-stone-700">
                        <tr>
                            <th class="rounded-tl-xl border-b border-stone-100 px-4 py-3">Tanggal</th>
                            <th class="border-b border-stone-100 px-4 py-3">Nomor Tiket</th>
                            <th class="border-b border-stone-100 px-4 py-3">Nama</th>
                            <th class="border-b border-stone-100 px-4 py-3">Status</th>
                            <th class="border-b border-stone-100 px-4 py-3">Durasi</th>
                            <th class="rounded-tr-xl border-b border-stone-100 px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservationDetails as $detail)
                            <tr class="border-b border-stone-50 hover:bg-stone-50">
                                <td class="whitespace-nowrap px-4 py-4">{{ $detail['tanggal_jam'] }}</td>
                                <td class="px-4 py-4 font-bold text-stone-900">{{ $detail['kode_reservasi'] }}</td>
                                <td class="px-4 py-4">{{ $detail['nama_lengkap'] }}</td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-stone-700">{{ $detail['status'] }}</span>
                                </td>
                                <td class="px-4 py-4 font-semibold text-stone-800">{{ $detail['durasi_tatap_muka'] }}</td>
                                <td class="px-4 py-4">
                                    <button
                                        type="button"
                                        class="open-detail-modal rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-red-700"
                                        data-detail='@json($detail)'
                                    >
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-stone-500">Belum ada data layanan yang sesuai dengan filter saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $reservations->links() }}
            </div>
        </section>
    </div>
</div>

<div id="service-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-black/50 px-4 py-6 sm:py-10">
    <div class="max-h-[calc(100vh-3rem)] w-full max-w-3xl overflow-y-auto rounded-3xl border border-stone-200 bg-white p-6 shadow-2xl md:p-7">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-700">Detail Layanan Agent</p>
                <h3 id="modal-ticket-code" class="mt-2 text-xl font-black text-stone-900">-</h3>
            </div>
            <button type="button" id="close-service-detail-modal" class="rounded-xl border border-stone-200 px-3 py-2 text-xs font-bold text-stone-600 hover:bg-stone-100">Tutup</button>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Nama</p>
                <p id="modal-name" class="mt-1 text-sm font-bold text-stone-900">-</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Jabatan</p>
                <p id="modal-position" class="mt-1 text-sm font-bold text-stone-900">-</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Instansi</p>
                <p id="modal-company" class="mt-1 text-sm font-bold text-stone-900">-</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Jenis Layanan</p>
                <p id="modal-service-type" class="mt-1 text-sm font-bold text-stone-900">-</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 sm:col-span-2">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Jadwal Reservasi</p>
                <p id="modal-schedule" class="mt-1 text-sm font-bold text-stone-900">-</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Status</p>
                <p id="modal-status" class="mt-1 text-sm font-bold text-stone-900">-</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Durasi Tatap Muka</p>
                <p id="modal-duration" class="mt-1 text-sm font-bold text-stone-900">-</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Mulai Tatap Muka</p>
                <p id="modal-start-time" class="mt-1 text-sm font-bold text-stone-900">-</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Selesai Tatap Muka</p>
                <p id="modal-end-time" class="mt-1 text-sm font-bold text-stone-900">-</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 sm:col-span-2">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Detail Keperluan Reservasi</p>
                <p id="modal-need-detail" class="mt-1 text-sm leading-relaxed text-stone-700">-</p>
            </div>
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 sm:col-span-2">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-700">Catatan Agent</p>
                <p id="modal-note" class="mt-1 text-sm leading-relaxed text-red-900">-</p>
                <a id="modal-note-file" href="#" target="_blank" rel="noopener" class="mt-3 hidden rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100">Lihat File Catatan</a>
            </div>
            <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 sm:col-span-2">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-blue-700">Dokumen Tamu</p>
                <p class="mt-1 text-xs text-blue-900">File lampiran yang diupload oleh tamu saat pendaftaran</p>
                <a id="modal-tamu-file" href="#" target="_blank" rel="noopener" class="mt-3 hidden rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100">Lihat Dokumen Tamu</a>
            </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('service-detail-modal');
        const closeButton = document.getElementById('close-service-detail-modal');
        const detailButtons = document.querySelectorAll('.open-detail-modal');

        const modalElements = {
            ticketCode: document.getElementById('modal-ticket-code'),
            name: document.getElementById('modal-name'),
            position: document.getElementById('modal-position'),
            company: document.getElementById('modal-company'),
            serviceType: document.getElementById('modal-service-type'),
            schedule: document.getElementById('modal-schedule'),
            status: document.getElementById('modal-status'),
            duration: document.getElementById('modal-duration'),
            startTime: document.getElementById('modal-start-time'),
            endTime: document.getElementById('modal-end-time'),
            needDetail: document.getElementById('modal-need-detail'),
            note: document.getElementById('modal-note'),
            noteFile: document.getElementById('modal-note-file'),
            tamuFile: document.getElementById('modal-tamu-file'),
        };

        const openModal = (detail) => {
            modalElements.ticketCode.textContent = detail.kode_reservasi || '-';
            modalElements.name.textContent = detail.nama_lengkap || '-';
            modalElements.position.textContent = detail.jabatan || '-';
            modalElements.company.textContent = detail.asal_pt || '-';
            modalElements.serviceType.textContent = detail.jenis_layanan || '-';
            modalElements.schedule.textContent = detail.tanggal_jam || '-';
            modalElements.status.textContent = detail.status || '-';
            modalElements.duration.textContent = detail.durasi_tatap_muka || '-';
            modalElements.startTime.textContent = detail.waktu_mulai_tatap_muka || '-';
            modalElements.endTime.textContent = detail.waktu_selesai_tatap_muka || '-';
            modalElements.needDetail.textContent = detail.detail_keperluan || '-';
            modalElements.note.textContent = detail.catatan_teks || '-';

            if (detail.catatan_file_url) {
                modalElements.noteFile.href = detail.catatan_file_url;
                modalElements.noteFile.textContent = `Lihat File Catatan (${detail.catatan_file_name || 'file'})`;
                modalElements.noteFile.classList.remove('hidden');
                modalElements.noteFile.classList.add('inline-flex');
            } else {
                modalElements.noteFile.href = '#';
                modalElements.noteFile.classList.remove('inline-flex');
                modalElements.noteFile.classList.add('hidden');
            }

            if (detail.tamu_lampiran_url) {
                modalElements.tamuFile.href = detail.tamu_lampiran_url;
                modalElements.tamuFile.textContent = `Lihat Dokumen Tamu (${detail.tamu_lampiran_name || 'file'})`;
                modalElements.tamuFile.classList.remove('hidden');
                modalElements.tamuFile.classList.add('inline-flex');
            } else {
                modalElements.tamuFile.href = '#';
                modalElements.tamuFile.classList.remove('inline-flex');
                modalElements.tamuFile.classList.add('hidden');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.scrollTop = 0;
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        detailButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const detail = JSON.parse(button.dataset.detail || '{}');
                openModal(detail);
            });
        });

        closeButton?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    });
</script>
@endpush

@endsection
