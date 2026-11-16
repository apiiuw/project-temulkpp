<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_import_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('filename');
            $table->string('source', 50)->default('lkpp_gsheet');
            $table->foreignId('uploaded_by')->constrained('superadmins');
            $table->enum('status', ['uploaded', 'validated', 'ready', 'imported', 'failed', 'cancelled'])->default('uploaded');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->unsignedInteger('warning_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->timestamps();
        });

        Schema::create('reservation_import_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('reservation_import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('raw_data');
            $table->json('normalized_data')->nullable();
            $table->enum('status', ['valid', 'invalid', 'warning', 'imported', 'skipped'])->default('invalid');
            $table->json('errors')->nullable();
            $table->json('warnings')->nullable();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->timestamps();
            $table->index(['batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_import_rows');
        Schema::dropIfExists('reservation_import_batches');
    }
};
