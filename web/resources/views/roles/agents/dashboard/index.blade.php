@extends('layouts.agent')
@section('container')

<div class="px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-4xl border border-stone-200/80 bg-white/90 p-6 shadow-[0_24px_60px_rgba(120,53,15,0.10)] backdrop-blur-xl sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-red-700">Dashboard Agent</p>
                    <h1 class="mt-3 text-3xl font-black text-stone-900 sm:text-4xl">Selamat datang, {{ auth()->user()->name }}</h1>
                    <p class="mt-4 text-sm leading-relaxed text-stone-600">
                        Ini adalah pusat kerja harian agent. Urutannya: cek tugas, buka jadwal, mulai tatap muka, lalu selesaikan layanan dan isi catatan hasil pelayanan.
                    </p>
                </div>

                <div class="rounded-[28px] border border-red-100 bg-[linear-gradient(145deg,#fff7ed_0%,#ffffff_100%)] p-5 shadow-sm lg:max-w-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-red-700">Informasi Akun</p>
                    <p class="mt-3 text-lg font-bold text-stone-900">{{ auth()->user()->email }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600">
                        Akun ini aktif sebagai agent internal TemuLKPP dan hanya dapat mengakses fitur operasional agent.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6">
            <section class="rounded-[28px] border border-red-200/80 bg-[linear-gradient(135deg,#fff4f2_0%,#ffffff_100%)] p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Alur Kerja Hari Ini</p>
                        <h2 class="mt-2 text-xl font-black text-stone-900">Tugas yang perlu Anda tangani hari ini</h2>
                    </div>
                    <a href="{{ route('agent.jadwal') }}" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700">
                        Buka Jadwal
                    </a>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-4">
                    <div class="rounded-2xl border border-stone-200 bg-white px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Total Jadwal</p>
                        <p class="mt-3 text-3xl font-black text-stone-900">{{ number_format($taskSummary['todayTotal'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Perlu Aksi</p>
                        <p class="mt-3 text-3xl font-black text-amber-800">{{ number_format($taskSummary['needsAction'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-orange-700">Sedang Berlangsung</p>
                        <p class="mt-3 text-3xl font-black text-orange-800">{{ number_format($taskSummary['inProgress'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Selesai Hari Ini</p>
                        <p class="mt-3 text-3xl font-black text-emerald-800">{{ number_format($taskSummary['completedToday'] ?? 0) }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Tahapan Kerja Agent</p>
                <div class="mt-5 grid gap-4 lg:grid-cols-4">
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                        <p class="text-sm font-black text-stone-900">1. Cek tugas</p>
                        <p class="mt-2 text-sm leading-relaxed text-stone-600">Lihat jadwal hari ini dan reservasi yang sudah check-in.</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                        <p class="text-sm font-black text-stone-900">2. Mulai tatap muka</p>
                        <p class="mt-2 text-sm leading-relaxed text-stone-600">Tekan tombol mulai saat tamu siap dilayani.</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                        <p class="text-sm font-black text-stone-900">3. Isi catatan layanan</p>
                        <p class="mt-2 text-sm leading-relaxed text-stone-600">Tuliskan hasil pelayanan dan unggah file bila diperlukan.</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                        <p class="text-sm font-black text-stone-900">4. Selesaikan layanan</p>
                        <p class="mt-2 text-sm leading-relaxed text-stone-600">Pastikan status berubah menjadi selesai setelah layanan ditutup.</p>
                    </div>
                </div>
            </section>

            <div class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Tugas Utama</p>
                <h2 class="mt-2 text-xl font-black text-stone-900">Reservasi yang siap Anda tangani</h2>
                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left text-sm text-stone-600">
                        <thead class="bg-stone-50 text-xs font-bold uppercase text-stone-700">
                            <tr>
                                <th class="rounded-tl-xl border-b border-stone-100 px-4 py-3">Waktu</th>
                                <th class="border-b border-stone-100 px-4 py-3">Kode</th>
                                <th class="border-b border-stone-100 px-4 py-3">Nama Tamu</th>
                                <th class="border-b border-stone-100 px-4 py-3">Status</th>
                                <th class="border-b border-stone-100 px-4 py-3 rounded-tr-xl">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($myReservations as $reservation)
                                <tr class="border-b border-stone-50 hover:bg-stone-50">
                                    <td class="px-4 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($reservation->tanggal_jam)->format('H:i') }}</td>
                                    <td class="px-4 py-4 font-bold text-stone-900">{{ $reservation->kode_reservasi }}</td>
                                    <td class="px-4 py-4">{{ $reservation->nama_lengkap }}</td>
                                    <td class="px-4 py-4">
                                        @if($reservation->status === 'checked_in_front_desk')
                                            <span class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700">Siap Dilayani</span>
                                        @elseif($reservation->status === 'in_progress')
                                            <span class="rounded-lg bg-orange-100 px-3 py-1.5 text-xs font-semibold text-orange-700">Sedang Berlangsung</span>
                                        @else
                                            <span class="rounded-lg bg-stone-100 px-3 py-1.5 text-xs font-semibold text-stone-700">Selesai</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($reservation->status === 'checked_in_front_desk')
                                            <form action="{{ route('agent.tatap-muka.start', $reservation->kode_reservasi) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-red-700">Mulai</button>
                                            </form>
                                        @elseif($reservation->status === 'in_progress')
                                            <a href="{{ route('agent.tatap-muka', $reservation->kode_reservasi) }}" class="rounded-lg bg-orange-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-orange-700">Lanjutkan</a>
                                        @else
                                            <span class="rounded-lg bg-stone-100 px-3 py-1.5 text-xs font-semibold text-stone-700">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-stone-500">Belum ada reservasi yang siap Anda tangani saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-stone-400">Referensi Operasional</p>
                <h2 class="mt-2 text-xl font-black text-stone-900">Reservasi hari ini yang belum check-in</h2>
                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left text-sm text-stone-600">
                        <thead class="bg-stone-50 text-xs font-bold uppercase text-stone-700">
                            <tr>
                                <th class="rounded-tl-xl border-b border-stone-100 px-4 py-3">Waktu</th>
                                <th class="border-b border-stone-100 px-4 py-3">Kode</th>
                                <th class="border-b border-stone-100 px-4 py-3">Nama Tamu</th>
                                <th class="border-b border-stone-100 px-4 py-3 rounded-tr-xl">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingReservations as $reservation)
                                <tr class="border-b border-stone-50 hover:bg-stone-50">
                                    <td class="px-4 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($reservation->tanggal_jam)->format('H:i') }}</td>
                                    <td class="px-4 py-4 font-bold text-stone-900">{{ $reservation->kode_reservasi }}</td>
                                    <td class="px-4 py-4">{{ $reservation->nama_lengkap }}</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-lg bg-orange-100 px-3 py-1.5 text-xs font-semibold text-orange-700">Menunggu check-in</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-stone-500">Semua reservasi hari ini sudah check-in atau sudah tidak aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Superset Analysis Section --}}
    <div class="mx-auto mt-6 max-w-7xl">
        <div class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Analisis Performa</p>
                    <h2 class="mt-2 text-xl font-black text-stone-900">Statistik Pelayanan Anda</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">Live Analytics</span>
                </div>
            </div>
            
            <div class="mt-6">
                <div id="superset-container" class="w-full min-h-[750px] rounded-2xl border border-stone-100 bg-stone-50 overflow-hidden relative">
                    {{-- Placeholder/Loading state --}}
                    <div id="superset-loader" class="absolute inset-0 flex flex-col items-center justify-center bg-stone-50/80 backdrop-blur-sm z-10">
                        <div class="h-10 w-10 animate-spin rounded-full border-4 border-red-600 border-t-transparent"></div>
                        <p class="mt-4 text-sm font-bold text-stone-500 uppercase tracking-widest">Memuat Grafik Performa...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/@superset-ui/embedded-sdk"></script>
<style>
    /* Force Superset iframe to fill container */
    #superset-container iframe {
        width: 100% !important;
        height: 750px !important;
        border: none !important;
        background: transparent !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.getElementById('superset-container');
        const loader = document.getElementById('superset-loader');
        
        const DASHBOARD_ID = '{{ env("SUPERSET_DASHBOARD_ID") }}';
        const SUPERSET_DOMAIN = '{{ env("SUPERSET_URL", "http://localhost:8088") }}';

        if (!DASHBOARD_ID || DASHBOARD_ID === 'your-dashboard-uuid-here') {
            loader.innerHTML = `
                <div class="p-8 text-center">
                    <p class="text-amber-600 font-bold uppercase tracking-widest text-xs">Konfigurasi Diperlukan</p>
                    <p class="mt-2 text-stone-500 text-sm">Silakan masukkan SUPERSET_DASHBOARD_ID di file .env.</p>
                </div>
            `;
            return;
        }

        try {
            await supersetEmbeddedSdk.embedDashboard({
                id: DASHBOARD_ID,
                supersetDomain: SUPERSET_DOMAIN,
                mountPoint: container,
                fetchGuestToken: async () => {
                    const response = await fetch(`{{ route('superset.guest-token') }}?dashboardId=${DASHBOARD_ID}`);
                    if (!response.ok) {
                        const errData = await response.json();
                        throw new Error(errData.error || 'Gagal mengambil token');
                    }
                    const data = await response.json();
                    return data.token;
                },
                dashboardUiConfig: {
                    hideTitle: true,
                    hideControls: true,
                    hideChartControls: true,
                },
            });
            
            // Hide loader once the SDK has initialized the iframe
            loader.style.display = 'none';

        } catch (error) {
            console.error('Superset Embedding Error:', error);
            loader.innerHTML = `
                <div class="p-8 text-center text-red-600">
                    <p class="font-bold">Gagal memuat grafik</p>
                    <p class="text-sm mt-1">${error.message}</p>
                </div>
            `;
        }
    });
</script>
@endpush

@endsection
