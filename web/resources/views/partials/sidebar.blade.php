{{--
    Pemilih sidebar berdasarkan halaman yang dibuka ($sidebarRole), bukan guard
    mana yang sedang login. Kalau berdasarkan guard, satu browser yang login
    beberapa aktor akan menampilkan sidebar aktor yang salah.

    Fallback ke deteksi guard hanya dipakai bila $sidebarRole tidak diberikan.
--}}
@php
    $sidebarRole = $sidebarRole ?? null;
@endphp

@if ($sidebarRole === 'superadmin')
    @include('partials.sidebar-superadmin')
@elseif ($sidebarRole === 'pimpinan')
    @include('partials.sidebar-pimpinan')
@elseif ($sidebarRole === 'agent')
    @include('partials.sidebar-agent')
@elseif (auth()->guard('superadmin')->check())
    @include('partials.sidebar-superadmin')
@elseif (auth()->guard('pimpinan')->check())
    @include('partials.sidebar-pimpinan')
@else
    @include('partials.sidebar-agent')
@endif
