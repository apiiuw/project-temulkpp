<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\LkppReservationImportService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

final class ImportLkppReservations extends Command
{
    protected $signature = 'reservations:import-lkpp
        {file : CSV file path}
        {--dry-run : Validate and report without inserting rows}';

    protected $description = 'Import LKPP reservation data from CSV';

    public function handle(): int
    {
        $path = $this->resolvePath((string) $this->argument('file'));

        if (! is_file($path) || ! is_readable($path)) {
            $this->error("CSV file not readable: {$path}");

            return self::FAILURE;
        }

        $file = new UploadedFile($path, basename($path), 'text/csv', null, true);
        $service = app(LkppReservationImportService::class);
        $batch = $service->validate($file, (int) config('app.import_system_user_id', 1));
        $this->line("Valid rows: {$batch->valid_rows}");
        $this->line("Invalid rows: {$batch->invalid_rows}");
        $this->line("Warnings: {$batch->warning_rows}");
        if ($this->option('dry-run')) {
            $this->info('Dry run: no rows inserted.');
            return $batch->invalid_rows > 0 ? self::FAILURE : self::SUCCESS;
        }
        $this->line('Imported rows: ' . $service->import($batch));
        return $batch->invalid_rows > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1) {
            return $path;
        }

        $applicationPath = base_path($path);
        if (is_file($applicationPath)) {
            return $applicationPath;
        }

        return dirname(base_path()) . DIRECTORY_SEPARATOR . $path;
    }
}
