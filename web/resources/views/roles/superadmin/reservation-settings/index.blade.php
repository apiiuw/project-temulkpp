@extends('layouts.superadmin')

@section('container')
<div class="min-h-full pb-12">
    <!-- Header Section -->
    <div class="border-b border-stone-200 bg-white/40 px-6 py-10 backdrop-blur-md lg:px-10">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-red-700">Master Konfigurasi</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-stone-900 md:text-4xl">{{ $title }}</h1>
                    <p class="mt-2 text-sm leading-relaxed text-stone-500">Kelola alur, waktu, dan jenis layanan yang tersedia pada formulir reservasi publik.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
        @if(session('success'))
            {{-- SweetAlert2 will handle this via script below --}}
        @endif

        <div class="grid gap-8 lg:grid-cols-12">
            <!-- General Settings -->
            <div class="lg:col-span-7">
                <form action="{{ route('superadmin.reservation-settings.update') }}" method="POST" class="space-y-8">
                    @csrf
                    <div class="overflow-hidden rounded-[32px] border border-stone-200 bg-white shadow-sm transition-all hover:shadow-md">
                        <div class="border-b border-stone-100 bg-stone-50/50 px-7 py-6">
                            <h2 class="text-lg font-black text-stone-900">Parameter Dasar Reservasi</h2>
                            <p class="mt-1 text-xs font-medium text-stone-500">Atur batasan kuota dan jadwal operasional harian.</p>
                        </div>
                        
                        <div class="space-y-6 p-7">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-stone-400">Hari Operasional</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        @php
                                            $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 0 => 'Minggu'];
                                            $availableDays = json_decode($settings['available_days'] ?? '[]', true);
                                        @endphp
                                        @foreach($days as $val => $label)
                                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-stone-100 bg-stone-50 px-4 py-3 transition-all hover:border-red-200 hover:bg-red-50/30 has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                                                <input type="checkbox" name="available_days[]" value="{{ $val }}" {{ in_array($val, $availableDays) ? 'checked' : '' }} class="h-4 w-4 rounded border-stone-300 text-red-600 focus:ring-red-500/20">
                                                <span class="text-sm font-bold text-stone-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold uppercase tracking-wider text-stone-400">Maks. Reservasi / Slot</label>
                                        <div class="relative">
                                            <input type="number" name="max_reservations_per_slot" value="{{ $settings['max_reservations_per_slot'] }}" 
                                                class="w-full rounded-2xl border-stone-200 bg-stone-50 px-5 py-4 text-sm font-bold focus:border-red-500 focus:ring-red-500/10">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-xs font-bold text-stone-400">Orang</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold uppercase tracking-wider text-stone-400">Durasi Per Sesi</label>
                                        <div class="relative">
                                            <input type="number" name="consultation_duration_minutes" value="{{ $settings['consultation_duration_minutes'] }}" 
                                                class="w-full rounded-2xl border-stone-200 bg-stone-50 px-5 py-4 text-sm font-bold focus:border-red-500 focus:ring-red-500/10">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-xs font-bold text-stone-400">Menit</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-stone-100">

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div class="space-y-4">
                                    <h3 class="text-sm font-black text-stone-900">Sesi Pagi</h3>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Mulai</label>
                                            <input type="time" name="morning_start" value="{{ $settings['morning_start'] }}" class="w-full rounded-xl border-stone-200 bg-stone-50 px-4 py-3 text-sm font-bold">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Selesai</label>
                                            <input type="time" name="morning_end" value="{{ $settings['morning_end'] }}" class="w-full rounded-xl border-stone-200 bg-stone-50 px-4 py-3 text-sm font-bold">
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <h3 class="text-sm font-black text-stone-900">Sesi Siang</h3>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Mulai</label>
                                            <input type="time" name="afternoon_start" value="{{ $settings['afternoon_start'] }}" class="w-full rounded-xl border-stone-200 bg-stone-50 px-4 py-3 text-sm font-bold">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Selesai</label>
                                            <input type="time" name="afternoon_end" value="{{ $settings['afternoon_end'] }}" class="w-full rounded-xl border-stone-200 bg-stone-50 px-4 py-3 text-sm font-bold">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 bg-stone-50 px-7 py-5 sm:flex-row sm:items-center">
                            <button type="submit" class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-stone-900 px-8 py-4 text-sm font-black text-white shadow-lg transition-all hover:bg-stone-800 active:scale-95">
                                Simpan Perubahan Parameter
                            </button>
                            <button type="button" onclick="document.getElementById('reset-settings-form').submit()" class="flex items-center justify-center gap-2 rounded-2xl border border-stone-200 bg-white px-8 py-4 text-sm font-bold text-stone-500 shadow-sm transition-all hover:bg-stone-50 hover:text-red-600 active:scale-95">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Reset Default
                            </button>
                        </div>
                    </div>
                </form>

                <form id="reset-settings-form" action="{{ route('superadmin.reservation-settings.reset') }}" method="POST" class="hidden" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan semua pengaturan ke nilai awal?')">
                    @csrf
                </form>
            </div>

            <!-- Service Types -->
            <div class="lg:col-span-5">
                <div class="space-y-8">
                    <!-- Add Service Type -->
                    <div class="overflow-hidden rounded-[32px] border border-stone-200 bg-white shadow-sm">
                        <div class="border-b border-stone-100 bg-stone-50/50 px-7 py-6">
                            <h2 class="text-lg font-black text-stone-900">Jenis Layanan</h2>
                            <p class="mt-1 text-xs font-medium text-stone-500">Daftar layanan yang dapat dipilih oleh tamu.</p>
                        </div>
                        
                        <div class="p-7">
                            <form action="{{ route('superadmin.service-types.store') }}" method="POST" class="flex gap-3">
                                @csrf
                                <input type="text" name="name" placeholder="Nama layanan baru..." required class="flex-1 rounded-2xl border-stone-200 bg-stone-50 px-5 py-3.5 text-sm font-bold focus:border-red-500 focus:ring-red-500/10">
                                <button type="submit" class="shrink-0 rounded-2xl bg-red-600 px-5 py-3.5 text-sm font-black text-white shadow-md hover:bg-red-700">Tambah</button>
                            </form>

                            <div class="mt-8 space-y-3">
                                @foreach($serviceTypes as $type)
                                    <div class="group flex items-center justify-between gap-4 rounded-[24px] border border-stone-100 bg-stone-50/50 p-4 transition-all hover:border-red-100 hover:bg-white hover:shadow-sm">
                                        <div class="flex flex-1 items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $type->is_active ? 'bg-red-50 text-red-600' : 'bg-stone-100 text-stone-400' }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                            </div>
                                            <span class="text-sm font-bold {{ $type->is_active ? 'text-stone-900' : 'text-stone-400 line-through' }}">{{ $type->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                            <form action="{{ route('superadmin.service-types.toggle', $type) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" title="{{ $type->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" class="rounded-xl border border-stone-200 bg-white p-2 text-stone-500 hover:text-red-600">
                                                    @if($type->is_active)
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    @else
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    @endif
                                                </button>
                                            </form>
                                            <form action="{{ route('superadmin.service-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Hapus jenis layanan ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="rounded-xl border border-stone-200 bg-white p-2 text-stone-500 hover:text-red-600">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#000000',
                background: '#ffffff',
                customClass: {
                    popup: 'rounded-[32px]',
                    confirmButton: 'rounded-2xl px-6 py-3 text-sm font-bold'
                }
            });
        @endif
    });
</script>
@endpush
