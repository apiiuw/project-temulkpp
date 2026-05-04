<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'jabatan')) {
                $table->string('jabatan', 100)->nullable()->after('nama_lengkap');
            }

            if (! Schema::hasColumn('reservations', 'jenis_layanan')) {
                $table->string('jenis_layanan', 20)->nullable()->after('asal_pt');
            }

            if (! Schema::hasColumn('reservations', 'detail_keperluan')) {
                $table->text('detail_keperluan')->nullable()->after('jenis_layanan');
            }
        });

        if (Schema::hasColumn('reservations', 'jenis_kebutuhan')) {
            DB::table('reservations')
                ->whereNull('jenis_layanan')
                ->update([
                    'jenis_layanan' => DB::raw('jenis_kebutuhan'),
                ]);
        }

        if (Schema::hasColumn('reservations', 'deskripsi_keluhan')) {
            DB::table('reservations')
                ->whereNull('detail_keperluan')
                ->update([
                    'detail_keperluan' => DB::raw('deskripsi_keluhan'),
                ]);
        }

        DB::table('reservations')
            ->whereNull('jabatan')
            ->update([
                'jabatan' => 'Belum diisi',
            ]);

    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'detail_keperluan')) {
                $table->dropColumn('detail_keperluan');
            }

            if (Schema::hasColumn('reservations', 'jenis_layanan')) {
                $table->dropColumn('jenis_layanan');
            }

            if (Schema::hasColumn('reservations', 'jabatan')) {
                $table->dropColumn('jabatan');
            }
        });
    }
};
