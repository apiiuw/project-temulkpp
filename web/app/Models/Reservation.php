<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        // Mode data uji: request ?data=uji menyaring SELURUH query reservasi
        // pada request tersebut (kartu, tabel, agregat, dan section penilaian).
        // Penandanya kolom source = 'data-uji' ATAU reservasi bulan Oktober 2026
        // ke atas — selaras dengan kolom jenis_data di dataset Superset.
        static::addGlobalScope('only_test_data', function (Builder $query): void {
            if (request()->query('data') !== 'uji') {
                return;
            }

            $query->where(function (Builder $q): void {
                $q->where('source', 'data-uji')->orWhere('tanggal_jam', '>=', '2026-10-01');
            });
        });

        static::creating(function (self $reservation): void {
            if ($reservation->tahun === null && $reservation->tanggal_jam !== null) {
                $reservation->tahun = $reservation->tanggal_jam->year;
            }
        });
    }

    /**
     * Pisahkan data uji dari data historis pada query reservasi.
     *
     * Definisi selaras dengan kolom ``jenis_data`` di dataset Superset:
     * Data Uji = source = 'data-uji' ATAU tanggal_jam >= 2026-10-01.
     * Historis = kebalikannya (source berbeda dari 'data-uji'/NULL dan
     * tanggal_jam sebelum Oktober 2026/NULL).
     *
     * @param  string  $jenis  'uji' | 'historis'
     */
    public function scopeJenisData(Builder $query, string $jenis): Builder
    {
        $isTestData = function (Builder $q): void {
            $q->where('source', 'data-uji')->orWhere('tanggal_jam', '>=', '2026-10-01');
        };

        if ($jenis === 'uji') {
            return $query->where($isTestData);
        }

        return $query
            ->where(function (Builder $q): void {
                $q->where('source', '!=', 'data-uji')->orWhereNull('source');
            })
            ->where(function (Builder $q): void {
                $q->whereNull('tanggal_jam')->orWhere('tanggal_jam', '<', '2026-10-01');
            });
    }

    protected function casts(): array
    {
        return [
            'tanggal_jam' => 'datetime',
            'checked_in_at' => 'datetime',
            'waktu_mulai_tatap_muka' => 'datetime',
            'waktu_selesai_tatap_muka' => 'datetime',
            'source_row' => 'integer',
            'tahun' => 'integer',
            'rating_kemudahan' => 'integer',
            'rating_efisiensi' => 'integer',
            'rating_sikap' => 'integer',
            'rating_kejelasan' => 'integer',
            'rating_kepuasan' => 'integer',
            'rated_at' => 'datetime',
        ];
    }

    public const RATING_QUESTIONS = [
        'rating_kemudahan' => 'Kemudahan Verifikasi',
        'rating_efisiensi' => 'Efisiensi Waktu',
        'rating_sikap' => 'Sikap Pelayanan',
        'rating_kejelasan' => 'Kejelasan & Solusi',
        'rating_kepuasan' => 'Kepuasan Keseluruhan',
    ];

    public const RATING_STATEMENTS = [
        'rating_kemudahan' => 'Proses verifikasi kedatangan di meja resepsionis berjalan dengan mudah dan lancar.',
        'rating_efisiensi' => 'Waktu tunggu dari awal kedatangan hingga dilayani oleh petugas (Agent) berjalan dengan efisien.',
        'rating_sikap' => 'Petugas (Agent) melayani sesi konsultasi dengan ramah, sopan, dan penuh perhatian.',
        'rating_kejelasan' => 'Petugas (Agent) memberikan penjelasan dan solusi yang akurat serta mudah dipahami.',
        'rating_kepuasan' => 'Secara keseluruhan, saya merasa puas dengan kualitas layanan tatap muka yang diberikan.',
    ];

    /**
     * Rata-rata kelima jawaban Likert, atau null bila belum dinilai sama sekali.
     * Nilai null sengaja dipertahankan agar rata-rata tidak tercemar data historis.
     */
    public function getAverageRatingAttribute(): ?float
    {
        $scores = collect(self::RATING_QUESTIONS)
            ->keys()
            ->map(fn ($column) => $this->getAttribute($column))
            ->filter(fn ($score) => $score !== null);

        if ($scores->isEmpty()) {
            return null;
        }

        return round($scores->avg(), 1);
    }

    /**
     * true bila semua pertanyaan sudah dijawab.
     */
    public function getHasCompleteRatingAttribute(): bool
    {
        return collect(self::RATING_QUESTIONS)
            ->keys()
            ->every(fn ($column) => $this->getAttribute($column) !== null);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'checked_in_front_desk' => 'Sudah Check-In',
            'in_progress' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            'expired_front_desk' => 'Kedaluwarsa',
            'dialihkan_ke_l2' => 'Dialihkan ke L2',
            default => str_replace('_', ' ', ucfirst((string) $this->status)),
        };
    }

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
