<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;

/**
 * Menyiapkan data untuk section "Penilaian Tamu" (distribusi rating + komentar terbaru)
 * yang ditampilkan di dashboard superadmin, pimpinan, dan agent.
 */
trait BuildsRatingDashboard
{
    /**
     * Jenis data yang dipakai section penilaian, mengikuti filter "Jenis Data"
     * di dashboard: 'uji' (?data=uji) atau 'historis' (default).
     */
    private function ratingDataJenis(): string
    {
        return request()->query('data') === 'uji' ? 'uji' : 'historis';
    }

    /**
     * Reservasi yang sudah dinilai lengkap oleh tamu (kelima kolom rating terisi).
     *
     * @param  int|null  $agentId  null = seluruh agent, selain itu hanya reservasi milik agent tsb.
     * @param  string|null  $jenis  'uji' | 'historis' | null = ikut filter jenis data request.
     */
    private function ratedReservations(?int $agentId = null, ?string $jenis = null): Builder
    {
        $query = Reservation::query()->jenisData($jenis ?? $this->ratingDataJenis());

        if ($agentId !== null) {
            $query->where('agent_id', $agentId);
        }

        foreach (array_keys(Reservation::RATING_QUESTIONS) as $ratingColumn) {
            $query->whereNotNull($ratingColumn);
        }

        return $query;
    }

    /**
     * Rata-rata tiap kolom rating, sebagai stdClass tanpa cast model.
     *
     * toBase() sengaja dipakai: model Reservation meng-cast kolom rating ke integer,
     * sehingga AVG desimal (mis. 3.5) akan terpotong menjadi 3 bila lewat Eloquent.
     *
     * @param  int|null  $agentId  null = seluruh agent, selain itu hanya reservasi milik agent tsb.
     * @param  string|null  $jenis  'uji' | 'historis' | null = ikut filter jenis data request.
     * @return object|null
     */
    private function ratingAveragesFor(?int $agentId = null, ?string $jenis = null): ?object
    {
        $ratingColumns = array_keys(Reservation::RATING_QUESTIONS);

        return $this->ratedReservations($agentId, $jenis)
            ->toBase()
            ->selectRaw(
                collect($ratingColumns)
                    ->map(fn (string $column) => "AVG({$column}) AS {$column}")
                    ->implode(', ')
            )
            ->first();
    }

    /**
     * Jumlah jawaban per skala 1–5, sebagai stdClass tanpa cast model.
     *
     * @param  int|null  $agentId  null = seluruh agent, selain itu hanya reservasi milik agent tsb.
     * @param  string|null  $jenis  'uji' | 'historis' | null = ikut filter jenis data request.
     * @return object|null
     */
    private function ratingAnswerCountsFor(?int $agentId = null, ?string $jenis = null): ?object
    {
        $ratingColumns = array_keys(Reservation::RATING_QUESTIONS);

        return $this->ratedReservations($agentId, $jenis)
            ->toBase()
            ->selectRaw(
                collect(range(1, 5))
                    ->map(function (int $score) use ($ratingColumns) {
                        $cases = collect($ratingColumns)
                            ->map(fn (string $column) => "CASE WHEN {$column} = {$score} THEN 1 ELSE 0 END")
                            ->implode(' + ');

                        return "SUM({$cases}) AS skor_{$score}";
                    })
                    ->implode(', ')
            )
            ->first();
    }

    /**
     * Data yang dibutuhkan view partial `partials.rating-dashboard`.
     *
     * Selalu diisi pada mode uji maupun historis: pada data historis rating
     * dan komentar masih kosong, sehingga partial menampilkan empty state.
     *
     * @param  int|null  $agentId  null = agregat semua agent, angka = hanya agent tersebut.
     * @param  string|null  $jenis  'uji' | 'historis' | null = ikut filter jenis data request.
     * @return array<string, mixed>
     */
    private function ratingDashboardData(?int $agentId = null, ?string $jenis = null): array
    {
        $jenis = $jenis ?? $this->ratingDataJenis();

        $commentQuery = Reservation::query()
            ->jenisData($jenis)
            ->whereNotNull('rating_comment')
            ->where('rating_comment', '!=', '');

        if ($agentId !== null) {
            $commentQuery->where('agent_id', $agentId);
        }

        $ratingComments = $commentQuery
            ->with('agent:id,name')
            ->latest('rated_at')
            ->limit(10)
            ->get();

        return [
            'ratingJenis' => $jenis,
            'ratingQuestions' => Reservation::RATING_QUESTIONS,
            'ratingAverages' => $this->ratingAveragesFor($agentId, $jenis),
            'ratingAnswerCounts' => $this->ratingAnswerCountsFor($agentId, $jenis),
            'ratingComments' => $ratingComments,
        ];
    }
}
