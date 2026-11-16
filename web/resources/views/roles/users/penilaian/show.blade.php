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
                        Berikan penilaian Anda untuk layanan tatap muka yang telah diterima.
                    </p>
                </div>

                @if (session('rating_success'))
                    <div class="mb-8 p-8 rounded-2xl bg-linear-to-br from-emerald-50 to-teal-50 border border-emerald-100 shadow-sm">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 bg-emerald-500/20 text-emerald-500 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-emerald-800">{{ session('rating_success') }}</h3>
                            <p class="mt-2 text-emerald-600/80">
                                Masukan Anda sangat berharga untuk peningkatan layanan TemuLKPP.
                            </p>


                            <a
                                href="{{ route('penilaian') }}"
                                class="mt-6 inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-6 py-3 text-sm font-bold text-red-700 transition-transform duration-300 hover:-translate-y-0.5"
                            >
                                Nilai Reservasi Lain
                            </a>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-8 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-red-800">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Detail Layanan</p>
                        <a
                            href="{{ route('penilaian') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-4 py-2 text-xs font-bold text-red-700 transition-transform duration-300 hover:-translate-y-0.5"
                        >
                            Ganti Kode Reservasi
                        </a>
                    </div>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Kode Reservasi</p>
                            <p class="mt-1 text-sm font-black tracking-[0.18em] text-gray-900">{{ $reservation->kode_reservasi }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Status Layanan</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->status_label }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Nama Tamu</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->nama_lengkap }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Jabatan</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->jabatan }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Instansi / Perusahaan</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->asal_pt }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Layanan Oleh</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->agent->name ?? 'Belum ditentukan' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Jenis Layanan</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $reservation->jenis_layanan }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Jadwal Layanan</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($reservation->tanggal_jam)->translatedFormat('d F Y, H:i') }} WIB
                            </p>
                        </div>
                    </div>
                </div>

                @if (! session('rating_success'))
                    @if ($ratingState && $ratingState['can_rate'])
                        <div class="mt-8 rounded-3xl border border-red-100 bg-red-50/60 p-6 shadow-sm">
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Beri Penilaian</p>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">
                                Seberapa puas Anda dengan layanan yang diterima hari ini?
                            </p>

                            <form method="POST" action="{{ route('penilaian.store', $reservation->kode_reservasi) }}" class="mt-6">
                                @csrf

                                <fieldset>
                                    <legend class="text-sm font-bold text-gray-700">
                                        Penilaian Layanan <span class="text-red-600">*</span>
                                    </legend>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Pilih satu jawaban untuk setiap pernyataan di bawah ini.
                                    </p>

                                    @php($likertLabels = [1 => 'Sangat Tidak Setuju', 2 => 'Tidak Setuju', 3 => 'Netral', 4 => 'Setuju', 5 => 'Sangat Setuju'])

                                    <div class="mt-4 space-y-4">
                                        @foreach (\App\Models\Reservation::RATING_QUESTIONS as $column => $questionLabel)
                                            <div class="rounded-2xl border border-gray-200 bg-white p-4">
                                                <p class="text-sm font-bold text-gray-800">
                                                    {{ $loop->iteration }}. {{ $questionLabel }} <span class="text-red-600">*</span>
                                                </p>
                                                <p class="mt-1 text-xs leading-relaxed text-gray-500">
                                                    "{{ \App\Models\Reservation::RATING_STATEMENTS[$column] }}"
                                                </p>

                                                <div class="mt-3 grid grid-cols-5 gap-2">
                                                    @for ($score = 1; $score <= 5; $score++)
                                                        <input
                                                            type="radio"
                                                            id="{{ $column }}-{{ $score }}"
                                                            name="{{ $column }}"
                                                            value="{{ $score }}"
                                                            class="peer/q sr-only"
                                                            {{ (int) old($column) === $score ? 'checked' : '' }}
                                                            required
                                                        >
                                                        <label
                                                            for="{{ $column }}-{{ $score }}"
                                                            title="{{ $score }} - {{ $likertLabels[$score] }}"
                                                            class="cursor-pointer rounded-xl border border-gray-200 bg-white px-1 py-2 text-center transition-all duration-200 hover:border-red-300 hover:bg-red-50 peer-checked/q:border-red-600 peer-checked/q:bg-red-600 peer-checked/q:text-white peer-focus/q:ring-2 peer-focus/q:ring-red-500 peer-focus/q:ring-offset-2"
                                                        >
                                                            <span class="block text-sm font-black">{{ $score }}</span>
                                                            <span class="mt-0.5 hidden text-[10px] leading-tight sm:block">{{ $likertLabels[$score] }}</span>
                                                        </label>
                                                    @endfor
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <p class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                        @foreach ($likertLabels as $legendScore => $legendLabel)
                                            <span>
                                                <span class="font-bold text-gray-700">{{ $legendScore }}</span>
                                                = {{ $legendLabel }}
                                            </span>
                                        @endforeach
                                    </p>
                                </fieldset>

                                <div class="mt-6">
                                    <label for="rating_comment" class="block mb-2 text-sm font-bold text-gray-700">
                                        Komentar <span class="font-medium text-gray-400">(opsional)</span>
                                    </label>
                                    <textarea
                                        id="rating_comment"
                                        name="rating_comment"
                                        rows="4"
                                        maxlength="{{ $maxCommentLength }}"
                                        placeholder="Ceritakan pengalaman Anda selama menggunakan layanan kami..."
                                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none text-sm resize-none"
                                    >{{ old('rating_comment') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">Maksimal {{ $maxCommentLength }} karakter.</p>
                                </div>

                                <div class="mt-6">
                                    <button
                                        type="submit"
                                        class="w-full inline-flex items-center justify-center rounded-2xl bg-linear-to-r from-red-700 to-black px-6 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(127,29,29,0.25)] transition-transform duration-300 hover:-translate-y-0.5"
                                    >
                                        Kirim Penilaian
                                    </button>
                                    <p class="mt-3 text-xs text-gray-500 text-center">
                                        Penilaian hanya dapat diberikan satu kali dan hanya pada hari yang sama dengan selesainya layanan.
                                    </p>
                                </div>
                            </form>
                        </div>
                    @elseif ($ratingState && ! $ratingState['can_rate'])
                        <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">
                            <p class="text-sm font-bold uppercase tracking-[0.2em]">Penilaian Tidak Tersedia</p>
                            <p class="mt-2 text-sm leading-relaxed">{{ $ratingState['block_reason'] }}</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
