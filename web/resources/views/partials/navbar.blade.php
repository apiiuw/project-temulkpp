<nav class="absolute inset-x-0 top-0 z-40 bg-transparent">
  <div class="max-w-screen-xl mx-auto px-4 pt-4 md:pt-6">
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-[28px] border border-white/35 bg-white/12 px-3 py-3 shadow-[0_18px_46px_rgba(0,0,0,0.12)] backdrop-blur-xl md:flex-nowrap md:px-4">
      <a href="{{ route('reservasi') }}" class="inline-flex items-center rounded-2xl border border-white/50 bg-white/80 px-3 py-2 shadow-sm transition-transform duration-300 hover:-translate-y-0.5">
        <img src="{{ asset('img/icon/logo/logoBlackRed.png') }}" class="h-10 md:h-12" alt="Logo" />
      </a>

      <button
        data-collapse-toggle="navbar-default"
        type="button"
        class="inline-flex items-center justify-center rounded-2xl border border-white/50 bg-white/80 p-3 text-sm text-gray-700 shadow-sm transition-colors duration-300 hover:bg-white md:hidden"
        aria-controls="navbar-default"
        aria-expanded="false"
      >
        <span class="sr-only">Open main menu</span>
        <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
        </svg>
      </button>

      <div class="hidden w-full md:block md:w-auto md:shrink-0" id="navbar-default">
        <ul class="mt-2 flex flex-col gap-2 rounded-[24px] border border-white/50 bg-white/88 p-2 shadow-lg backdrop-blur-xl md:mt-0 md:flex-row md:items-center md:gap-2 md:rounded-full md:bg-white/72">
          <li>
            <a
              href="{{ route('reservasi') }}"
              class="block rounded-full px-5 py-3 text-sm font-semibold transition-all duration-300 md:px-6 @if(request()->routeIs('reservasi')) bg-linear-to-r from-red-700 to-red-600 text-white shadow-[0_10px_20px_rgba(185,28,28,0.28)] @else text-gray-800 hover:bg-red-50 hover:text-red-700 @endif"
              aria-current="{{ request()->routeIs('reservasi') ? 'page' : 'false' }}"
            >
              Reservasi
            </a>
          </li>
          <li>
            <a
              href="{{ route('atur-ulang-jadwal') }}"
              class="block rounded-full px-5 py-3 text-sm font-semibold transition-all duration-300 md:px-6 @if(request()->routeIs('atur-ulang-jadwal')) bg-linear-to-r from-red-700 to-red-600 text-white shadow-[0_10px_20px_rgba(185,28,28,0.28)] @else text-gray-800 hover:bg-red-50 hover:text-red-700 @endif"
              aria-current="{{ request()->routeIs('atur-ulang-jadwal') ? 'page' : 'false' }}"
            >
              Atur Ulang Jadwal
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
