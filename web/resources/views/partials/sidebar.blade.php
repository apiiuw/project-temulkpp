@if (auth()->guard('superadmin')->check())
    @include('partials.sidebar-superadmin')
@elseif (auth()->guard('pimpinan')->check())
    @include('partials.sidebar-pimpinan')
@else
    @include('partials.sidebar-agent')
@endif
