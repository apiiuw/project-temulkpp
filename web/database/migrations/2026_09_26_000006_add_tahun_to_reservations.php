<?php

declare(strict_types=1);

use App\Models\Reservation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->unsignedSmallInteger('tahun')->nullable()->index()->after('tanggal_jam');
        });

        Reservation::query()->whereNotNull('tanggal_jam')->chunkById(500, function ($reservations): void {
            foreach ($reservations as $reservation) {
                $reservation->updateQuietly(['tahun' => $reservation->tanggal_jam->year]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropIndex(['tahun']);
            $table->dropColumn('tahun');
        });
    }
};
