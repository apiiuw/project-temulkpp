@php
    $paginator->onEachSide(1);
    $window = \Illuminate\Pagination\UrlWindow::make($paginator);
    $elements = array_filter([
        $window['first'],
        is_array($window['slider']) ? '...' : null,
        $window['slider'],
        is_array($window['last']) ? '...' : null,
        $window['last'],
    ]);
@endphp

@if ($paginator->total() > 0)
    <nav class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="Navigasi pagination">
        <p class="text-xs font-medium text-stone-500 sm:text-sm">
            Menampilkan
            <span class="font-bold text-stone-700">{{ $paginator->firstItem() }}</span>
            sampai
            <span class="font-bold text-stone-700">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-bold text-stone-700">{{ $paginator->total() }}</span>
            data
        </p>

        @if ($paginator->hasPages())
            <div class="flex flex-wrap items-center gap-1.5" role="list">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex min-h-10 items-center rounded-xl border border-stone-200 bg-stone-100 px-3 text-xs font-bold text-stone-400" aria-disabled="true">
                        Sebelumnya
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex min-h-10 items-center rounded-xl border border-stone-200 bg-white px-3 text-xs font-bold text-stone-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">
                        Sebelumnya
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl px-2 text-xs font-bold text-stone-400" aria-hidden="true">{{ $element }}</span>
                    @else
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl bg-stone-900 px-3 text-xs font-black text-white" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl border border-stone-200 bg-white px-3 text-xs font-bold text-stone-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex min-h-10 items-center rounded-xl border border-stone-200 bg-white px-3 text-xs font-bold text-stone-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">
                        Berikutnya
                    </a>
                @else
                    <span class="inline-flex min-h-10 items-center rounded-xl border border-stone-200 bg-stone-100 px-3 text-xs font-bold text-stone-400" aria-disabled="true">
                        Berikutnya
                    </span>
                @endif
            </div>
        @endif
    </nav>
@endif
