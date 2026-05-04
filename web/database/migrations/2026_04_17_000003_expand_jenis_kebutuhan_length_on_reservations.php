<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'jenis_kebutuhan')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `reservations` MODIFY `jenis_kebutuhan` VARCHAR(100) NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'jenis_kebutuhan')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `reservations` MODIFY `jenis_kebutuhan` ENUM(\'SPSE\',\'Non SPSE\') NOT NULL');
        }
    }
};
