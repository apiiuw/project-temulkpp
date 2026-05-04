<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'jenis_layanan')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `reservations` MODIFY `jenis_layanan` VARCHAR(100) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'jenis_layanan')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `reservations` MODIFY `jenis_layanan` VARCHAR(20) NULL');
        }
    }
};
