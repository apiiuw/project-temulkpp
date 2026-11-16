<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating_kemudahan')->nullable()->after('catatan_tatap_muka');
            $table->unsignedTinyInteger('rating_efisiensi')->nullable()->after('rating_kemudahan');
            $table->unsignedTinyInteger('rating_sikap')->nullable()->after('rating_efisiensi');
            $table->unsignedTinyInteger('rating_kejelasan')->nullable()->after('rating_sikap');
            $table->unsignedTinyInteger('rating_kepuasan')->nullable()->after('rating_kejelasan');
            $table->text('rating_comment')->nullable()->after('rating_kepuasan');
            $table->timestamp('rated_at')->nullable()->after('rating_comment');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'rating_kemudahan',
                'rating_efisiensi',
                'rating_sikap',
                'rating_kejelasan',
                'rating_kepuasan',
                'rating_comment',
                'rated_at',
            ]);
        });
    }
};
