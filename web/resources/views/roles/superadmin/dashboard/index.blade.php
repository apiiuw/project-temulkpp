@extends('layouts.superadmin')
@section('container')

<div class="px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-4xl border border-stone-200/80 bg-white/90 p-6 shadow-[0_24px_60px_rgba(120,53,15,0.10)] backdrop-blur-xl sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-red-700">Dashboard Superadmin</p>
                    <h1 class="mt-3 text-3xl font-black text-stone-900 sm:text-4xl">Selamat datang, {{ auth()->guard('superadmin')->user()->name }}</h1>
                    <p class="mt-4 text-sm leading-relaxed text-stone-600">
                        Halaman ini adalah pusat kendali bagi superadmin untuk memantau aktivitas operasional TemuLKPP.
                    </p>
                </div>

                <div class="rounded-[28px] border border-red-100 bg-[linear-gradient(145deg,#fff7ed_0%,#ffffff_100%)] p-5 shadow-sm lg:max-w-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-red-700">Informasi Akun</p>
                    <p class="mt-3 text-lg font-bold text-stone-900">{{ auth()->guard('superadmin')->user()->email }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600">
                        Akun ini aktif sebagai superadmin internal TemuLKPP dengan akses ke ringkasan operasional seluruh agent.
                    </p>
                </div>
            </div>
        </div>

        <div id="overview" class="mt-6 grid gap-5 md:grid-cols-2">
            <div class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-stone-400">Layanan Selesai</p>
                <h2 class="mt-3 text-3xl font-black text-stone-900">{{ number_format($completedCount) }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-stone-600">Total layanan yang sudah selesai dari seluruh agent.</p>
            </div>

            <div class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-stone-400">Layanan Tidak Selesai</p>
                <h2 class="mt-3 text-3xl font-black text-stone-900">{{ number_format($unfinishedCount) }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-stone-600">Reservasi yang masih pending, check-in, atau sedang diproses.</p>
            </div>

        </div>
        
        {{-- Superset Analysis Section --}}
        <div class="mt-6">
            <div class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Analisis Operasional Superadmin</p>
                        <h2 class="mt-2 text-xl font-black text-stone-900">Dashboard Statistik Global</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">Live Analytics</span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <div id="superset-container" class="w-full min-h-[1050px] rounded-2xl border border-stone-100 bg-stone-50 overflow-hidden relative">
                        {{-- Placeholder/Loading state --}}
                        <div id="superset-loader" class="absolute inset-0 flex flex-col items-center justify-center bg-stone-50/80 backdrop-blur-sm z-10">
                            <div class="h-10 w-10 animate-spin rounded-full border-4 border-red-600 border-t-transparent"></div>
                            <p class="mt-4 text-sm font-bold text-stone-500 uppercase tracking-widest">Memuat Grafik Performa...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <section id="layanan-selesai" class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Ringkasan Operasional</p>
                <h2 class="mt-2 text-xl font-black text-stone-900">Layanan Selesai</h2>
                <p class="mt-2 text-sm text-stone-600">Ringkasan layanan yang sudah selesai secara keseluruhan.</p>

                <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200">
                    <table class="w-full text-left text-sm text-stone-600">
                        <thead class="bg-stone-50 text-xs font-bold uppercase text-stone-700">
                            <tr>
                                <th class="px-4 py-3">Waktu Selesai</th>
                                <th class="px-4 py-3">Kode</th>
                                <th class="px-4 py-3">Nama</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($completedReservations as $reservation)
                                <tr class="border-t border-stone-100 hover:bg-stone-50">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ optional($reservation->waktu_selesai_tatap_muka)->format('d M Y, H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold text-stone-900">{{ $reservation->kode_reservasi }}</td>
                                    <td class="px-4 py-3">{{ $reservation->nama_lengkap }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-stone-500">Belum ada layanan yang selesai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="layanan-tidak-selesai" class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Ringkasan Operasional</p>
                <h2 class="mt-2 text-xl font-black text-stone-900">Layanan Tidak Selesai</h2>
                <p class="mt-2 text-sm text-stone-600">Data reservasi yang masih berjalan dan perlu perhatian operasional.</p>

                <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200">
                    <table class="w-full text-left text-sm text-stone-600">
                        <thead class="bg-stone-50 text-xs font-bold uppercase text-stone-700">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Kode</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($unfinishedReservations as $reservation)
                                <tr class="border-t border-stone-100 hover:bg-stone-50">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ optional($reservation->tanggal_jam)->format('d M Y, H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold text-stone-900">{{ $reservation->kode_reservasi }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">{{ str_replace('_', ' ', $reservation->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-stone-500">Semua layanan sudah selesai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>


            <section id="performa-agent" class="rounded-[28px] border border-stone-200/80 bg-white p-6 shadow-[0_18px_45px_rgba(28,25,23,0.06)] xl:col-span-2">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Kinerja Agent</p>
                <h2 class="mt-2 text-xl font-black text-stone-900">Performa Agent</h2>
                <p class="mt-2 text-sm text-stone-600">Perbandingan singkat performa agent berdasarkan layanan yang ditangani.</p>

                <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200">
                    <table class="w-full text-left text-sm text-stone-600">
                        <thead class="bg-stone-50 text-xs font-bold uppercase text-stone-700">
                            <tr>
                                <th class="px-4 py-3">Agent</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Selesai</th>
                                <th class="px-4 py-3">Berjalan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agentPerformance as $agent)
                                <tr class="border-t border-stone-100 hover:bg-stone-50">
                                    <td class="px-4 py-3 font-bold text-stone-900">{{ $agent->name }}</td>
                                    <td class="px-4 py-3">{{ number_format($agent->total_reservations_count) }}</td>
                                    <td class="px-4 py-3">{{ number_format($agent->completed_reservations_count) }}</td>
                                    <td class="px-4 py-3">{{ number_format($agent->active_reservations_count) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-stone-500">Belum ada data agent untuk ditampilkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/@superset-ui/embedded-sdk"></script>
<style>
    /* Force Superset iframe to fill container */
    #superset-container iframe {
        width: 100% !important;
        height: 1050px !important;
        border: none !important;
        background: transparent !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.getElementById('superset-container');
        const loader = document.getElementById('superset-loader');
        
        // Use the newly generated Superadmin Dashboard UUID directly
        const DASHBOARD_ID = 'da1f9b3b-8911-4eb7-a7eb-9df03b41bb1c';
        const SUPERSET_DOMAIN = '{{ env("SUPERSET_URL", "http://localhost:8088") }}';

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
