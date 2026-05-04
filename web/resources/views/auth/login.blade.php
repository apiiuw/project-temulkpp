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
    <body class="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(185,28,28,0.22),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(120,53,15,0.18),_transparent_34%),linear-gradient(140deg,#160f10_0%,#26191b_48%,#111113_100%)]">
        <div class="relative flex min-h-screen items-center justify-center px-4 py-10">
            <div class="absolute inset-0 bg-[linear-gradient(115deg,transparent_0%,rgba(255,255,255,0.04)_50%,transparent_100%)]"></div>

            <div class="relative z-10 grid w-full max-w-6xl overflow-hidden rounded-[36px] border border-white/10 bg-white/8 shadow-[0_35px_120px_rgba(0,0,0,0.42)] backdrop-blur-xl lg:grid-cols-[1.05fr_0.95fr]">
                <section class="hidden min-h-full flex-col justify-between border-r border-white/10 p-10 text-white lg:flex">
                    <div>
                        <div class="inline-flex rounded-2xl bg-white px-4 py-4 shadow-sm">
                            <img src="{{ asset('img/icon/logo/logoBlackRed.png') }}" alt="TemuLKPP" class="h-12">
                        </div>
                        <p class="mt-10 text-xs font-bold uppercase tracking-[0.34em] text-red-200">Akses Internal</p>
                        <h1 class="mt-4 max-w-lg text-4xl font-black leading-tight">
                            Login internal portal TemuLKPP.
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
                                <label for="email" class="block text-sm font-bold text-stone-800">Email Akun</label>
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
                                <label for="password" class="block text-sm font-bold text-stone-800">Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Masukkan password akun"
                                    class="mt-2 w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-900 outline-none transition-all duration-300 focus:border-red-300 focus:ring-4 focus:ring-red-500/10"
                                    required
                                >
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
