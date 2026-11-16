@extends('layouts.main')
@section('container')

<div class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 w-full h-full overflow-hidden z-0">
        <video
            autoplay
            muted
            loop
            playsinline
            class="absolute top-1/2 left-1/2 min-w-full min-h-full -translate-x-1/2 -translate-y-1/2 object-cover opacity-80"
        >
            <source src="https://player.vimeo.com/external/517604620.sd.mp4?s=d63892cfae8b15099238e888a7c2936316f499c8&profile_id=164&oauth2_token_id=57447761" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="absolute inset-0 bg-linear-to-br from-red-900/60 via-black/40 to-red-900/60 transition-opacity duration-1000"></div>
    </div>

    <div class="relative z-10 w-full max-w-5xl mx-auto px-4 pb-40 pt-36 md:pb-44 md:pt-44">
        <div class="bg-white/85 backdrop-blur-xl border border-white/30 rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-8 md:p-12">
                <div class="flex flex-col items-center mb-10 text-center">
                    <div class="p-3 bg-red-600/10 rounded-2xl mb-4">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-linear-to-r from-gray-900 to-red-700">
                        Penilaian Layanan
                    </h1>
                    <p class="mt-2 max-w-2xl text-gray-600 font-medium">
                        Masukkan kode reservasi Anda untuk menilai layanan tatap muka yang telah diterima.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-8 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-red-800">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-3xl border border-red-100 bg-white/90 p-6 shadow-sm">
                    <form method="GET" action="{{ route('penilaian') }}" class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                        <div>
                            <label for="kode_reservasi" class="block mb-2 text-sm font-bold text-gray-700">
                                Kode Reservasi
                            </label>
                            <input
                                type="text"
                                id="kode_reservasi"
                                name="kode_reservasi"
                                value="{{ old('kode_reservasi') }}"
                                placeholder="Contoh: RES-20261020-CMHC"
                                autocomplete="off"
                                autofocus
                                class="w-full px-4 py-3 uppercase tracking-[0.18em] bg-white border border-gray-200 rounded-2xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none text-center"
                                maxlength="17"
                                pattern="RES-[0-9]{8}-[A-Za-z0-9]{4}"
                                title="Gunakan format RES-YYYYMMDD-XXXX."
                            >
                            <p class="mt-2 text-xs text-gray-500">Kode tercantum pada QR code atau file PDF reservasi Anda.</p>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-600 to-red-800 px-6 py-3 text-sm font-bold text-white shadow-[0_10px_20px_rgba(220,38,38,0.28)] transition-transform duration-300 hover:-translate-y-0.5 mb-0 md:mb-6">
                            Konfirmasi
                        </button>
                    </form>
                </div>

                <div class="mt-8 rounded-2xl border border-gray-100 bg-white/70 px-5 py-4">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-gray-500">Ketentuan Penilaian</p>
                    <ul class="mt-3 space-y-2 text-sm leading-relaxed text-gray-600">
                        <li class="flex gap-2">
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500"></span>
                            Penilaian hanya tersedia setelah sesi tatap muka <span class="font-semibold text-gray-900">selesai</span>.
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500"></span>
                            Dapat diisi pada <span class="font-semibold text-gray-900">hari yang sama</span> dengan selesainya layanan.
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500"></span>
                            Setiap reservasi hanya dapat dinilai <span class="font-semibold text-gray-900">satu kali</span>.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
