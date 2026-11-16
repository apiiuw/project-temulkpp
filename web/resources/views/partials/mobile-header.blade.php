@php
    $mobileRole = $mobileRole ?? match (true) {
        auth()->guard('superadmin')->check() => 'Superadmin',
        auth()->guard('pimpinan')->check() => 'Pimpinan',
        auth()->guard('agent')->check() => 'Agent',
        default => 'Portal Internal',
    };
@endphp

<header class="app-mobile-header xl:hidden">
    <a href="{{ route('reservasi') }}" class="inline-flex min-w-0 items-center gap-3" aria-label="Kembali ke beranda TemuLKPP">
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-sm">
            <img src="{{ asset('img/icon/logo/logoGaruda.png') }}" alt="LKPP" class="h-full w-full object-contain">
        </span>
        <span class="min-w-0">
            <span class="block truncate text-[11px] font-bold uppercase tracking-[0.18em] text-red-700">TemuLKPP</span>
            <span class="block truncate text-sm font-black text-stone-900">{{ $mobileRole }}</span>
        </span>
    </a>

    <button
        type="button"
        class="app-mobile-menu-button"
        data-mobile-sidebar-open
        aria-controls="app-sidebar"
        aria-expanded="false"
        aria-label="Buka navigasi"
        onclick="document.body.classList.remove('sidebar-collapsed'); document.body.classList.add('mobile-sidebar-open'); this.setAttribute('aria-expanded', 'true');"
    >
        <span class="sr-only">Buka navigasi</span>
        <span class="app-menu-icon" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </button>
</header>

<div class="app-sidebar-backdrop" data-mobile-sidebar-close aria-hidden="true"></div>
