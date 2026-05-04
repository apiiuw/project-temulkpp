<aside class="sidebar-transition hidden w-96 shrink-0 border-r border-stone-200/80 bg-[linear-gradient(180deg,#2b1b12_0%,#4a2b17_100%)] text-white lg:flex lg:h-screen lg:flex-col lg:overflow-y-auto">
    <div class="border-b border-white/10 p-4">
        <div class="flex items-center gap-4">
            <div class="sidebar-transition flex items-center justify-center rounded-2xl bg-white px-3 py-3 shadow-sm shrink-0">
                {{-- Logo Full (With Text) --}}
                <img src="{{ asset('img/icon/logo/logoBlackRed.png') }}" alt="TemuLKPP" class="sidebar-full-logo h-10">
                {{-- Logo Mini (Garuda Only) --}}
                <img src="{{ asset('img/icon/logo/logoGaruda.png') }}" alt="LKPP" class="sidebar-mini-logo h-8">
            </div>
            <div class="sidebar-hide-content sidebar-transition overflow-hidden">
                <p class="text-xs font-bold uppercase tracking-[0.3em] text-amber-100 whitespace-nowrap">Portal Internal</p>
                <h1 class="mt-1 text-xl font-black tracking-[0.03em] whitespace-nowrap">Superadmin</h1>
            </div>
        </div>
    </div>

    <div class="flex flex-1 flex-col justify-between px-5 py-6">
        <div class="space-y-8">
            <div class="sidebar-hide-content sidebar-transition rounded-[28px] border border-white/10 bg-white/6 p-5 overflow-hidden">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-amber-100 whitespace-nowrap">Akun Aktif</p>
                <p class="mt-3 text-lg font-bold truncate">{{ auth()->guard('superadmin')->user()->name }}</p>
                <p class="mt-1 text-sm text-white/70 truncate">{{ auth()->guard('superadmin')->user()->email }}</p>
            </div>

            @php
                $roleMenus = \App\Models\RoleMenu::where('role', 'superadmin')->with('menu')->get()->pluck('menu');
            @endphp

            <nav class="space-y-2">
                @foreach ($roleMenus as $menu)
                    <a
                        href="{{ route($menu->route) }}"
                        title="{{ $menu->name }}"
                        class="sidebar-center-content sidebar-transition flex items-center gap-3 rounded-2xl px-4 py-2 text-sm font-bold transition-all duration-300 {{ request()->routeIs($menu->route . '*') ? 'bg-white text-amber-800 shadow-[0_14px_30px_rgba(255,255,255,0.16)]' : 'bg-white/5 text-white/82 hover:bg-white/12' }}"
                    >
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-current/15">
                            {!! $menu->icon !!}
                        </span>
                        <span class="sidebar-hide-content sidebar-transition whitespace-nowrap">{{ $menu->name }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-8 pt-6">
            @csrf
            <button type="submit" class="sidebar-center-content sidebar-transition flex w-full items-center justify-center rounded-2xl border border-white/14 bg-white/8 px-4 py-3 text-sm font-bold text-white transition-all duration-300 hover:bg-white/14">
                <span class="sidebar-hide-content sidebar-transition mr-2">Logout</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>
        </form>
    </div>
</aside>
