<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap', 50);
            $table->string('jabatan', 100);
            $table->string('asal_pt', 100);
            $table->enum('jenis_layanan', ['SPSE', 'Non SPSE']);
            $table->text('detail_keperluan');
            $table->string('lampiran', 255)->nullable();
            $table->dateTime('tanggal_jam');
            $table->string('kode_reservasi')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
