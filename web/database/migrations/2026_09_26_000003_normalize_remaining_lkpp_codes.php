<?php

declare(strict_types=1);

use App\Models\Reservation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Reservation::query()
            ->where('source', 'lkpp_gsheet')
            ->where('kode_reservasi', 'like', 'LKPP-%')
            ->chunkById(100, function ($reservations): void {
                foreach ($reservations as $reservation) {
                    do {
                        $code = sprintf(
                            'RES-%s-%s',
                            $reservation->tanggal_jam->format('Ymd'),
                            Str::upper(Str::random(4)),
                        );
                    } while (Reservation::where('kode_reservasi', $code)->exists());

                    Reservation::whereKey($reservation->id)->update([
                        'kode_reservasi' => $code,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Historical codes cannot be restored deterministically after normalization.
    }
};
