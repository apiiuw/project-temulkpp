<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_schedules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('day')->comment('1=Senin s/d 7=Minggu');
            $table->string('time', 5)->comment('Slot waktu HH:MM');
            $table->unsignedBigInteger('agent_id');
            $table->unsignedTinyInteger('parity')->default(0)->comment('0=minggu ganjil, 1=minggu genap');
            $table->timestamps();

            $table->unique(['day', 'time', 'parity', 'agent_id']);
            $table->index(['day', 'time', 'parity']);
            $table->foreign('agent_id')->references('id')->on('agents')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_schedules');
    }
};
