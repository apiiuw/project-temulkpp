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
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->timestamp('waktu_mulai_tatap_muka')->nullable();
            $table->timestamp('waktu_selesai_tatap_muka')->nullable();
            $table->text('catatan_tatap_muka')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn(['agent_id', 'waktu_mulai_tatap_muka', 'waktu_selesai_tatap_muka', 'catatan_tatap_muka']);
        });
    }
};
