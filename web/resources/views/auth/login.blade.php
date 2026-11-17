<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} | TemuLKPP</title>
        <link rel="icon" type="image/png" href="{{ asset('img/icon/logo/logoIcon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh overflow-x-clip overflow-y-auto bg-[radial-gradient(circle_at_top_left,_rgba(185,28,28,0.22),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(120,53,15,0.18),_transparent_34%),linear-gradient(140deg,#160f10_0%,#26191b_48%,#111113_100%)]">
        <div class="relative flex min-h-dvh items-center justify-center px-4 py-6 sm:py-10">
            <div class="absolute inset-0 bg-[linear-gradient(115deg,transparent_0%,rgba(255,255,255,0.04)_50%,transparent_100%)]"></div>

            <div class="relative z-10 grid w-full max-w-6xl overflow-hidden rounded-[36px] border border-white/10 bg-white/8 shadow-[0_35px_120px_rgba(0,0,0,0.42)] backdrop-blur-xl lg:grid-cols-[1.05fr_0.95fr]">
                <section class="hidden min-h-full flex-col justify-between border-r border-white/10 p-10 text-white lg:flex">
                    <div>
                        <div class="inline-flex rounded-2xl bg-white px-4 py-4 shadow-sm">
                            <img src="{{ asset('img/icon/logo/logoBlackRed.png') }}" alt="TemuLKPP" class="h-12">
                        </div>
                        <p class="mt-10 text-xs font-bold uppercase tracking-[0.34em] text-red-200">Akses Internal</p>
                        <h1 class="mt-4 max-w-lg text-4xl font-black leading-tight">
                            Masuk ke Portal Internal TemuLKPP.
                        </h1>
                        <p class="mt-5 max-w-xl text-sm leading-relaxed text-white/72">
                            Akses portal anda dengan melakukan otentikasi terlebih dahulu.
                        </p>
                    </div>
 
                </section>

                <section class="bg-[linear-gradient(180deg,rgba(255,251,245,0.98)_0%,rgba(252,245,235,0.98)_100%)] p-6 sm:p-8 lg:p-10">
                    <div class="mx-auto w-full max-w-md">
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-red-700">Portal Internal</p>
                        <h2 class="mt-3 text-3xl font-black text-stone-900">Masuk ke sistem</h2>
                        <p class="mt-2 text-sm leading-relaxed text-stone-600">
                            Masukkan email dan password akun Anda yang terdaftar.
                        </p>

                        @if ($errors->any())
                            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                            @csrf

                            <div>
                                <label for="email" class="block text-sm font-bold text-stone-800">Alamat Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="alamat@email.com"
                                    class="mt-2 w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-900 outline-none transition-all duration-300 focus:border-red-300 focus:ring-4 focus:ring-red-500/10"
                                    required
                                    autofocus
                                >
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-bold text-stone-800">Kata Sandi</label>
                                <div class="relative mt-2">
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        placeholder="Masukkan password akun"
                                        class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 pr-12 text-sm font-semibold text-stone-900 outline-none transition-all duration-300 focus:border-red-300 focus:ring-4 focus:ring-red-500/10"
                                        required
                                    >
                                    <button
                                        type="button"
                                        id="toggle-password"
                                        class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-stone-400 transition-colors hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500"
                                        aria-label="Tampilkan password"
                                        aria-pressed="false"
                                    >
                                        <svg id="password-eye-open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <svg id="password-eye-closed" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 3 18 18M10.58 10.58a2 2 0 0 0 2.83 2.83M9.88 5.09A10.6 10.6 0 0 1 12 4.75c6.25 0 9.75 7.25 9.75 7.25a17.7 17.7 0 0 1-3.08 3.92M6.61 6.61C3.95 8.42 2.25 12 2.25 12s3.5 7.25 9.75 7.25c1.27 0 2.42-.25 3.45-.65" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm font-medium text-stone-700">
                                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-stone-300 text-red-600 focus:ring-red-500">
                                Ingat sesi login di perangkat ini
                            </label>

                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#b91c1c_0%,#1f1313_100%)] px-6 py-3.5 text-sm font-bold text-white shadow-[0_18px_40px_rgba(127,29,29,0.24)] transition-transform duration-300 hover:-translate-y-0.5">
                                Akses Dashboard
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const passwordInput = document.getElementById('password');
                const togglePassword = document.getElementById('toggle-password');
                const eyeOpen = document.getElementById('password-eye-open');
                const eyeClosed = document.getElementById('password-eye-closed');

                togglePassword?.addEventListener('click', () => {
                    const isVisible = passwordInput.type === 'text';
                    passwordInput.type = isVisible ? 'password' : 'text';
                    togglePassword.setAttribute('aria-pressed', String(!isVisible));
                    togglePassword.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
                    eyeOpen.classList.toggle('hidden', !isVisible);
                    eyeClosed.classList.toggle('hidden', isVisible);
                });

                @if(session('error_inactive'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak',
                        text: '{{ session('error_inactive') }}',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#b91c1c',
                        background: '#ffffff',
                        customClass: {
                            popup: 'rounded-[32px]',
                            confirmButton: 'rounded-2xl px-6 py-3 text-sm font-bold'
                        }
                    });
                @endif
            });
        </script>
    </body>
</html>
