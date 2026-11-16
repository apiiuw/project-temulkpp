{{--
    Toggle "Hanya Data Uji".

    Menyaring SELURUH isi dashboard hanya ke baris data uji
    (ditandai kolom source = 'data-uji'; kodenya tetap berformat RES-YYYYMMDD-XXXX).
    Mekanismenya: global scope pada model App\Models\Reservation yang aktif
    saat request membawa ?data=uji, sehingga ikut menjangkau query kartu,
    tabel, agregat, dan section Penilaian Tamu tanpa perlu diubah satu per satu.

    @include('partials.data-filter')
--}}
@php($onlyTestData = $onlyTestData ?? request()->query('data') === 'uji')

<div class="mt-6 flex flex-col gap-3 rounded-[28px] border border-stone-200/80 bg-white p-4 shadow-[0_18px_45px_rgba(28,25,23,0.06)] sm:flex-row sm:items-center sm:justify-between sm:px-5">
    <div class="flex flex-wrap items-center gap-3">
        <span class="text-xs font-bold uppercase tracking-[0.22em] text-stone-400">Filter Data</span>

        <div class="inline-flex rounded-xl border border-stone-200 bg-stone-100 p-1">
            <a href="{{ request()->fullUrlWithoutQuery(['data']) }}"
               class="rounded-lg px-3 py-2 text-xs font-bold transition {{ $onlyTestData ? 'text-stone-500 hover:text-stone-700' : 'bg-white text-stone-900 shadow-sm' }}">
                Semua Data
            </a>
            <a href="{{ request()->fullUrlWithQuery(['data' => 'uji']) }}"
               class="rounded-lg px-3 py-2 text-xs font-bold transition {{ $onlyTestData ? 'bg-red-600 text-white shadow-sm' : 'text-stone-500 hover:text-stone-700' }}">
                Hanya Data Uji
            </a>
        </div>
    </div>

    @if ($onlyTestData)
        <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold leading-relaxed text-amber-800">
            Mode data uji aktif &mdash; hanya baris dengan <span class="font-mono">source = data-uji</span> yang ditampilkan.
        </p>
    @else
        <p class="text-xs font-medium text-stone-400">Menampilkan seluruh data reservasi.</p>
    @endif
</div>
