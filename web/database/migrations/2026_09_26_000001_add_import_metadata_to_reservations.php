<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->string('source', 50)->nullable()->index()->after('status');
            $table->string('source_agent_name', 100)->nullable()->after('source');
            $table->unsignedInteger('source_row')->nullable()->after('source_agent_name');
            $table->string('import_hash', 64)->nullable()->after('source_row');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropColumn(['source', 'source_agent_name', 'source_row', 'import_hash']);
        });
    }
};
