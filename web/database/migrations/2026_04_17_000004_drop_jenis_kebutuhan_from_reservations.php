<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'jenis_kebutuhan')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('jenis_kebutuhan');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations') || Schema::hasColumn('reservations', 'jenis_kebutuhan')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('jenis_kebutuhan', ['SPSE', 'Non SPSE'])->after('kode_reservasi');
        });
    }
};
