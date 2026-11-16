{{-- Section "Penilaian Tamu": distribusi rating + kotak komentar terbaru. --}}
{{-- Variabel dari controller: $ratingQuestions, $ratingAverages, $ratingAnswerCounts, $ratingComments, $ratingJenis --}}
{{-- $ratingJenis ('uji'|'historis') menentukan label badge & pesan empty state; defaultnya ikut ?data=uji pada URL. --}}
{{-- Opsional: $showAgentName (default true) — false untuk dashboard agent agar tidak menampilkan nama agent sendiri. --}}
@php
    $ratingQuestions = collect($ratingQuestions ?? []);
    $ratingAnswerCounts = $ratingAnswerCounts ?? null;
    $ratingComments = collect($ratingComments ?? []);
    $showAgentName = $showAgentName ?? true;

    // Jenis data yang sedang ditampilkan (ikut filter "Jenis Data" dashboard).
    $ratingJenis = $ratingJenis ?? (request()->query('data') === 'uji' ? 'uji' : 'historis');
    $isHistoricalData = $ratingJenis !== 'uji';
    $ratingDataLabel = $isHistoricalData ? 'Data Historis' : 'Data Uji';
    $ratingDataBadgeClass = $isHistoricalData
        ? 'bg-stone-100 text-stone-500'
        : 'bg-red-50 text-red-600';
@endphp

<div class="mt-6">
    <section class="rounded-[28px] border border-stone-200/80 bg-white p-5 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Penilaian Tamu</p>
                <h2 class="mt-1.5 text-lg font-black text-stone-900">Distribusi Rating</h2>
            </div>
            <span class="rounded-full px-3 py-1 text-[11px] font-bold {{ $ratingDataBadgeClass }}">{{ $ratingDataLabel }}</span>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            {{-- Rata-rata per pertanyaan --}}
            <div class="rounded-2xl border border-stone-200 bg-stone-50/60 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-500">Rata-rata per Pertanyaan</p>
                    <span class="text-[11px] text-stone-400">Skala 1.00 – 5.00</span>
                </div>

                @if ($ratingAverages === null || $ratingQuestions->every(fn ($_, $col) => empty($ratingAverages->{$col})))
                    <div class="flex h-56 flex-col items-center justify-center px-4 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-400 text-lg font-bold">★</div>
                        <p class="mt-3 text-xs font-bold text-stone-600">Belum ada penilaian masuk.</p>
                        <p class="mt-1 text-[11px] text-stone-400">
                            @if ($isHistoricalData)
                                Data historis diimpor sebelum fitur penilaian, jadi belum ada rating yang tercatat.
                            @else
                                Diagram otomatis terisi begitu tamu menyelesaikan penilaian.
                            @endif
                        </p>
                    </div>
                @else
                    @php
                        // avg() hanya meneruskan 1 argumen (nilai), jadi kunci diambil dulu lewat keys().
                        $overallAvg = $ratingQuestions->keys()
                            ->map(fn ($ratingColumn) => (float) ($ratingAverages->{$ratingColumn} ?? 0))
                            ->avg() ?? 0;
                        // Palet senada dengan dashboard Superset (supersetColors) agar satu irama.
                        $questionPalette = [
                            'rating_kemudahan' => ['#1FA8C9', '#e0f5fa'],
                            'rating_efisiensi' => ['#FF7F44', '#fff0e6'],
                            'rating_sikap' => ['#FCC700', '#fff8dd'],
                            'rating_kejelasan' => ['#454E7C', '#e8eaf2'],
                            'rating_kepuasan' => ['#5AC189', '#e4f7ed'],
                        ];
                    @endphp

                    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">
                        {{-- Donut Rata-rata Keseluruhan --}}
                        <div class="flex flex-col items-center justify-center sm:w-1/3">
                            <div class="relative flex h-28 w-28 items-center justify-center rounded-full shadow-inner"
                                 style="background: conic-gradient(#1FA8C9 0% {{ number_format(($overallAvg / 5) * 100, 2) }}%, #e7e5e4 {{ number_format(($overallAvg / 5) * 100, 2) }}% 100%);">
                                <div class="flex h-20 w-20 flex-col items-center justify-center rounded-full bg-white shadow-sm">
                                    <span class="text-xl font-black text-stone-900 leading-none">{{ number_format($overallAvg, 2) }}</span>
                                    <span class="mt-0.5 text-[10px] font-bold tracking-wider" style="color: #FCC700;">★★★★★</span>
                                    <span class="text-[9px] font-medium text-stone-400 uppercase tracking-tighter">dari 5.0</span>
                                </div>
                            </div>
                            <span class="mt-2 text-center text-[11px] font-bold text-stone-700">Skor Keseluruhan</span>
                        </div>

                        {{-- Mini Meter per Pertanyaan --}}
                        <div class="space-y-2.5 sm:w-2/3">
                            @foreach ($ratingQuestions as $ratingColumn => $ratingLabel)
                                @php
                                    $ratingAverage = (float) ($ratingAverages->{$ratingColumn} ?? 0);
                                    $pct = min(max(($ratingAverage / 5) * 100, 0), 100);
                                    $color = $questionPalette[$ratingColumn][0] ?? '#dc2626';
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-medium text-stone-700 truncate pr-2">{{ $ratingLabel }}</span>
                                        <span class="font-bold text-stone-900 shrink-0">{{ number_format($ratingAverage, 2) }}</span>
                                    </div>
                                    <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-stone-200">
                                        <div class="h-full rounded-full transition-all duration-500" style="width: {{ number_format($pct, 2) }}%; background-color: {{ $color }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Jawaban per kategori skala (PIE / DONUT CHART) --}}
            <div class="rounded-2xl border border-stone-200 bg-stone-50/60 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-500">Sebaran Jawaban Skala (1–5)</p>
                    <span class="text-[11px] text-stone-400">Pie Distribusi</span>
                </div>

                @php
                    // Warna mengikuti urutan palet Superset (supersetColors) seperti chart di dashboard Superset.
                    $scaleData = collect([
                        5 => ['label' => 'Sangat Baik (5)', 'color' => '#1FA8C9', 'count' => (int) ($ratingAnswerCounts?->skor_5 ?? 0)],
                        4 => ['label' => 'Baik (4)',         'color' => '#454E7C', 'count' => (int) ($ratingAnswerCounts?->skor_4 ?? 0)],
                        3 => ['label' => 'Cukup (3)',        'color' => '#5AC189', 'count' => (int) ($ratingAnswerCounts?->skor_3 ?? 0)],
                        2 => ['label' => 'Kurang (2)',       'color' => '#FF7F44', 'count' => (int) ($ratingAnswerCounts?->skor_2 ?? 0)],
                        1 => ['label' => 'Sangat Kurang (1)','color' => '#E04355', 'count' => (int) ($ratingAnswerCounts?->skor_1 ?? 0)],
                    ]);
                    $totalAnswers = (int) $scaleData->sum('count');

                    // Donat digambar sebagai <svg> (bukan conic-gradient) supaya tiap potongan
                    // bisa di-hover / diklik untuk memunculkan label, sama seperti chart Superset.
                    $cx = 100;
                    $cy = 100;
                    $rOuter = 96;
                    $rInner = 60;
                    $pieSlices = [];
                    $angle = -90; // mulai dari pukul 12, searah jarum jam

                    if ($totalAnswers > 0) {
                        foreach ($scaleData as $item) {
                            if ($item['count'] <= 0) {
                                continue;
                            }

                            $sweep = ($item['count'] / $totalAnswers) * 360;
                            if ($sweep > 359.9) {
                                $sweep = 359.9; // busur 360 penuh membuat path SVG degenerat
                            }

                            $a0 = deg2rad($angle);
                            $a1 = deg2rad($angle + $sweep);
                            $large = $sweep > 180 ? 1 : 0;

                            $x0o = $cx + $rOuter * cos($a0);
                            $y0o = $cy + $rOuter * sin($a0);
                            $x1o = $cx + $rOuter * cos($a1);
                            $y1o = $cy + $rOuter * sin($a1);
                            $x1i = $cx + $rInner * cos($a1);
                            $y1i = $cy + $rInner * sin($a1);
                            $x0i = $cx + $rInner * cos($a0);
                            $y0i = $cy + $rInner * sin($a0);

                            $pieSlices[] = [
                                'path' => sprintf(
                                    'M %.3f %.3f A %d %d 0 %d 1 %.3f %.3f L %.3f %.3f A %d %d 0 %d 0 %.3f %.3f Z',
                                    $x0o, $y0o, $rOuter, $rOuter, $large, $x1o, $y1o,
                                    $x1i, $y1i, $rInner, $rInner, $large, $x0i, $y0i
                                ),
                                'label' => $item['label'],
                                'color' => $item['color'],
                                'count' => $item['count'],
                                'pct'   => ($item['count'] / $totalAnswers) * 100,
                            ];

                            $angle += $sweep;
                        }
                    }
                @endphp

                @if ($totalAnswers === 0)
                    <div class="flex h-56 flex-col items-center justify-center px-4 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-400 font-black text-sm">0</div>
                        <p class="mt-3 text-xs font-bold text-stone-600">Belum ada jawaban masuk.</p>
                        <p class="mt-1 text-[11px] text-stone-400">
                            @if ($isHistoricalData)
                                Data historis belum memiliki jawaban skala dari tamu.
                            @else
                                Setiap 1 reservasi yang dinilai menyumbang 5 jawaban skala.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="mt-4 flex flex-col items-center gap-5 sm:flex-row sm:items-center">
                        {{-- Donut / Pie visual — SVG agar tiap potongan bisa di-hover & diklik --}}
                        <div class="relative shrink-0" style="width:128px;height:128px;">
                            <svg id="rating-pie-svg" viewBox="0 0 200 200" role="img"
                                 aria-label="Sebaran jawaban skala 1 sampai 5"
                                 style="display:block;width:100%;height:100%;">
                                <circle cx="100" cy="100" r="{{ $rInner }}" fill="#ffffff"></circle>
                                @foreach ($pieSlices as $slice)
                                    <path d="{{ $slice['path'] }}"
                                          fill="{{ $slice['color'] }}"
                                          data-label="{{ $slice['label'] }}"
                                          data-count="{{ number_format($slice['count']) }}"
                                          data-pct="{{ number_format($slice['pct'], 1) }}"
                                          data-color="{{ $slice['color'] }}"
                                          style="cursor:pointer;transition:opacity .2s ease;"></path>
                                @endforeach
                            </svg>
                            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                                <span class="text-lg font-black text-stone-900 leading-none">{{ number_format($totalAnswers) }}</span>
                                <span class="mt-0.5 text-[9px] font-bold uppercase tracking-wider text-stone-500">Jawaban</span>
                            </div>
                        </div>

                        {{-- Legend Pie Chart --}}
                        <div class="w-full space-y-1.5 text-xs">
                            @foreach ($scaleData as $score => $info)
                                @php
                                    $percent = $totalAnswers > 0 ? ($info['count'] / $totalAnswers) * 100 : 0;
                                @endphp
                                <div class="flex items-center justify-between rounded-lg px-2 py-1 transition-colors hover:bg-stone-100/70">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="h-3 w-3 shrink-0 rounded-full shadow-sm" style="background-color: {{ $info['color'] }};"></span>
                                        <span class="font-medium text-stone-700 truncate text-[11px]">{{ $info['label'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 text-[11px]">
                                        <span class="font-bold text-stone-900">{{ number_format($info['count']) }}</span>
                                        <span class="w-12 text-right font-medium text-stone-400">({{ number_format($percent, 1) }}%)</span>
                                    </div>
                                </div>
                            @endforeach

                            <div class="pt-1 text-right text-[10px] text-stone-400">
                                Dari {{ number_format($totalAnswers / max($ratingQuestions->count(), 1)) }} reservasi yang telah dinilai
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

{{-- Penilaian Tamu: kotak komentar terbaru --}}
<div class="mt-6">
    <section class="rounded-[28px] border border-stone-200/80 bg-white p-5 shadow-[0_18px_45px_rgba(28,25,23,0.06)]">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Penilaian Tamu</p>
                <h2 class="mt-1.5 text-lg font-black text-stone-900">Komentar Terbaru</h2>
            </div>
            <span class="rounded-full px-3 py-1 text-[11px] font-bold {{ $ratingDataBadgeClass }}">{{ $ratingDataLabel }}</span>
        </div>

        <div class="mt-4">
            @if ($ratingComments->isNotEmpty())
                <div id="rating-comment-scroll" class="space-y-2 overflow-y-auto">
                    @foreach ($ratingComments as $ratingComment)
                        <article class="rounded-xl border border-stone-200 bg-stone-50 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-0.5">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span class="text-xs" style="color: {{ $ratingComment->average_rating !== null && $star <= (int) round($ratingComment->average_rating) ? '#FCC700' : '#e7e5e4' }};">★</span>
                                    @endfor
                                </div>
                                <span class="shrink-0 text-[11px] font-medium text-stone-400">{{ optional($ratingComment->rated_at)->format('d M Y, H:i') ?? '-' }}</span>
                            </div>

                            <p class="mt-1.5 text-xs leading-relaxed text-stone-700">"{{ $ratingComment->rating_comment }}"</p>

                            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-stone-500">
                                <span class="font-bold text-stone-700">{{ $ratingComment->nama_lengkap }}</span>
                                @if ($showAgentName)
                                    <span>{{ $ratingComment->agent->name ?? 'Tanpa Agent' }}</span>
                                @endif
                                <span class="font-medium tracking-wide">{{ $ratingComment->kode_reservasi }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="flex h-28 flex-col items-center justify-center rounded-2xl border border-dashed border-stone-200 bg-stone-50 px-4 text-center">
                    <p class="text-xs font-bold text-stone-500">Belum ada komentar masuk.</p>
                    <p class="mt-0.5 text-[11px] text-stone-400">
                        @if ($isHistoricalData)
                            Data historis belum memiliki komentar dari tamu.
                        @else
                            Komentar tamu tampil otomatis di kotak ini.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var box = document.getElementById('rating-comment-scroll');
        if (!box) return;

        var items = box.querySelectorAll('article');
        if (!items.length) return;

        // Kunci tinggi kotak persis 3 baris komentar.
        var rowHeight = items[0].getBoundingClientRect().height;
        var gap = parseFloat(getComputedStyle(box).rowGap) || 0;
        box.style.height = (rowHeight * 3 + gap * 2) + 'px';

        // Tanpa auto-scroll: kotak di-scroll manual pakai kursor (scroll wheel).
        // Tingginya tetap dikunci setara 3 baris komentar supaya muat lalu bisa digeser ke bawah.

        // Tooltip donat "Sebaran Jawaban": hover tampil mengikuti kursor,
        // klik untuk menempelkan label, klik potongan yang sama / area kosong untuk melepas.
        var pieSvg = document.getElementById('rating-pie-svg');
        if (pieSvg) {
            var tip = document.createElement('div');
            tip.style.cssText = 'position:fixed;z-index:9999;display:none;pointer-events:none;background:#ffffff;'
                + 'border:1px solid #e7e5e4;border-radius:14px;padding:8px 12px;'
                + 'box-shadow:0 12px 28px rgba(28,25,23,0.16);font-size:12px;line-height:1.35;'
                + 'color:#1c1917;white-space:nowrap;font-family:inherit;';
            document.body.appendChild(tip);

            var piePaths = pieSvg.querySelectorAll('path');
            var piePinned = false;
            var piePinnedPath = null;

            function pieRender(p) {
                tip.innerHTML =
                    '<div style="display:flex;align-items:center;gap:7px;">'
                    + '<span style="width:9px;height:9px;border-radius:9999px;display:inline-block;background:'
                    + p.getAttribute('data-color') + ';"></span>'
                    + '<b style="font-weight:800;">' + p.getAttribute('data-label') + '</b>'
                    + '</div>'
                    + '<div style="display:flex;align-items:center;gap:14px;margin-top:4px;color:#57534e;">'
                    + '<span>count <b style="color:#1c1917;font-weight:800;">' + p.getAttribute('data-count') + '</b></span>'
                    + '<span><b style="color:#1c1917;font-weight:800;">' + p.getAttribute('data-pct') + '%</b></span>'
                    + '</div>';
            }

            function piePlace(e) {
                var w = tip.offsetWidth || 170;
                var h = tip.offsetHeight || 60;
                var left = e.clientX + 16;
                var top = e.clientY + 16;
                if (left + w > window.innerWidth - 8) { left = e.clientX - w - 16; }
                if (top + h > window.innerHeight - 8) { top = e.clientY - h - 16; }
                tip.style.left = Math.max(8, left) + 'px';
                tip.style.top = Math.max(8, top) + 'px';
            }

            function pieShow(p, e) { pieRender(p); tip.style.display = 'block'; piePlace(e); }
            function pieHide() { tip.style.display = 'none'; }

            function pieHighlight(target) {
                for (var i = 0; i < piePaths.length; i++) {
                    piePaths[i].style.opacity = (!target || piePaths[i] === target) ? '1' : '0.35';
                }
            }

            for (var i = 0; i < piePaths.length; i++) {
                (function (p) {
                    p.addEventListener('mouseenter', function (e) {
                        if (piePinned) return;
                        pieHighlight(p);
                        pieShow(p, e);
                    });
                    p.addEventListener('mousemove', function (e) {
                        if (piePinned) return;
                        piePlace(e);
                    });
                    p.addEventListener('mouseleave', function () {
                        if (piePinned) return;
                        pieHighlight(null);
                        pieHide();
                    });
                    p.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (piePinned && piePinnedPath === p) {
                            piePinned = false;
                            piePinnedPath = null;
                            pieHighlight(null);
                            pieHide();
                        } else {
                            piePinned = true;
                            piePinnedPath = p;
                            pieHighlight(p);
                            pieShow(p, e);
                        }
                    });
                })(piePaths[i]);
            }

            document.addEventListener('click', function () {
                if (!piePinned) return;
                piePinned = false;
                piePinnedPath = null;
                pieHighlight(null);
                pieHide();
            });
        }
    });
</script>
@endpush
