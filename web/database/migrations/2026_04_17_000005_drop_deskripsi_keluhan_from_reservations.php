<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'deskripsi_keluhan')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('deskripsi_keluhan');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations') || Schema::hasColumn('reservations', 'deskripsi_keluhan')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->text('deskripsi_keluhan')->nullable()->after('jenis_kebutuhan');
        });
    }
};
