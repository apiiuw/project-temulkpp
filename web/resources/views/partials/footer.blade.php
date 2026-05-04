
<footer class="relative z-30 -mt-24 bg-transparent md:-mt-28">
    <div class="max-w-screen-xl mx-auto px-4 pb-4 md:pb-6">
        <div class="flex flex-col gap-4 rounded-[28px] border border-white/35 bg-white/12 px-5 py-4 text-sm text-white shadow-[0_18px_46px_rgba(0,0,0,0.12)] backdrop-blur-xl md:flex-row md:items-center md:justify-between">
            <span class="text-sm text-white/90 sm:text-center">
                © 2026 <a href="{{ route('reservasi') }}" class="font-semibold text-white transition-colors duration-300 hover:text-red-100">TemuLKPP</a>. All Rights Reserved.
            </span>
            <ul class="flex flex-wrap items-center gap-2 text-sm font-medium">
                <li>
                    <a href="{{ route('reservasi') }}" class="inline-flex rounded-full border border-white/40 bg-white/78 px-4 py-2 text-gray-800 transition-all duration-300 hover:bg-red-50 hover:text-red-700">
                        Reservasi
                    </a>
                </li>
                <li>
                    <a href="{{ route('atur-ulang-jadwal') }}" class="inline-flex rounded-full border border-white/40 bg-white/78 px-4 py-2 text-gray-800 transition-all duration-300 hover:bg-red-50 hover:text-red-700">
                        Atur Ulang Jadwal
                    </a>
                </li>
            </ul>
        </div>
    </div>
</footer>
