<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Agent;
use App\Models\Reservation;
use App\Models\ReservationImportBatch;
use App\Models\ReservationImportRow;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class LkppReservationImportService
{
    /** @var array<string, int> */
    private array $agentIds = [];

    public function validate(UploadedFile $file, int $superadminId): ReservationImportBatch
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) throw new RuntimeException('File tidak dapat dibaca.');
        $headers = array_map(
            static fn ($header): string => trim(str_replace("\xEF\xBB\xBF", '', (string) $header)),
            fgetcsv($handle) ?: [],
        );
        $required = ['tanggal', 'waktu', 'jenis_layanan_konsultasi', 'status', 'agent'];
        if (array_diff($required, $headers) !== []) {
            fclose($handle);
            throw new RuntimeException('Header wajib: ' . implode(', ', $required));
        }

        $this->agentIds = Agent::query()->get(['id', 'name'])->mapWithKeys(
            fn (Agent $agent): array => [Str::lower(trim($agent->name)) => $agent->id]
        )->all();
        $batch = ReservationImportBatch::create([
            'filename' => Str::limit(basename($file->getClientOriginalName()), 255, ''),
            'uploaded_by' => $superadminId,
        ]);
        $valid = $invalid = $warnings = 0;
        $csvRows = [];
        while (($values = fgetcsv($handle)) !== false) {
            $raw = array_combine($headers, array_pad($values, count($headers), '')) ?: [];
            $csvRows[] = ['number' => count($csvRows) + 2, 'raw' => $raw];
        }
        fclose($handle);
        $total = (int) ceil(count($csvRows) / 2);

        for ($index = 0; $index < count($csvRows); $index += 2) {
            $first = $csvRows[$index];
            $second = $csvRows[$index + 1] ?? null;
            [$normalized, $errors, $rowWarnings] = $this->normalizePair($first['raw'], $second['raw'] ?? null);
            $status = $errors !== [] ? 'invalid' : ($rowWarnings !== [] ? 'warning' : 'valid');
            $valid += $errors === [] ? 1 : 0;
            $invalid += $errors !== [] ? 1 : 0;
            $warnings += $rowWarnings !== [] ? 1 : 0;
            $batch->rows()->create(['row_number' => $first['number'], 'raw_data' => ['mulai' => $first['raw'], 'selesai' => $second['raw'] ?? null], 'normalized_data' => $normalized, 'status' => $status, 'errors' => $errors ?: null, 'warnings' => $rowWarnings ?: null]);
        }
        $batch->update(['status' => $invalid === 0 ? 'ready' : 'validated', 'total_rows' => $total, 'valid_rows' => $valid, 'invalid_rows' => $invalid, 'warning_rows' => $warnings]);
        return $batch->fresh();
    }

    public function import(ReservationImportBatch $batch): int
    {
        $count = 0;

        DB::transaction(function () use ($batch, &$count): void {
            $lockedBatch = ReservationImportBatch::query()
                ->whereKey($batch->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBatch->invalid_rows > 0 || ! in_array($lockedBatch->status, ['ready', 'validated'], true)) {
                throw new RuntimeException('Batch belum siap diimport.');
            }

            $lockedBatch->rows()
                ->whereIn('status', ['valid', 'warning'])
                ->orderBy('id')
                ->each(function (ReservationImportRow $row) use (&$count): void {
                    $reservation = Reservation::create($row->normalized_data);
                    $row->update([
                        'status' => 'imported',
                        'reservation_id' => $reservation->id,
                    ]);
                    $count++;
                });

            $lockedBatch->update([
                'status' => 'imported',
                'imported_rows' => $count,
            ]);
        });

        return $count;
    }

    /** @return array{0: array<string, mixed>|null, 1: list<string>, 2: list<string>} */
    private function normalizePair(array $start, ?array $end): array
    {
        $errors = []; $warnings = [];
        $date = trim((string) ($start['tanggal'] ?? ''));
        $time = trim((string) ($start['waktu'] ?? ''));
        $startTime = null;
        try { $startTime = Carbon::createFromFormat('n/j/y H:i', trim(explode(' ', $date, 2)[0]) . ' ' . $time); }
        catch (\Throwable) { $startTime = null; $errors[] = 'Tanggal/waktu tidak valid.'; }
        $endTime = null;
        if (isset($startTime) && $startTime instanceof Carbon && $end !== null) {
            try { $endTime = Carbon::createFromFormat('n/j/y H:i', trim(explode(' ', (string) ($end['tanggal'] ?? ''), 2)[0]) . ' ' . trim((string) ($end['waktu'] ?? ''))); }
            catch (\Throwable) { $errors[] = 'Waktu selesai tidak valid.'; }
        } else { $errors[] = 'Baris waktu selesai tidak tersedia.'; }
        $service = trim((string) ($start['jenis_layanan_konsultasi'] ?? '')); if ($service === '') $errors[] = 'Jenis layanan kosong.';
        $sourceAgent = trim((string) ($start['agent'] ?? '')); $agentId = $this->agentIds[Str::lower($sourceAgent)] ?? null;
        if ($sourceAgent === '') $warnings[] = 'Agent kosong.'; elseif ($agentId === null) $errors[] = "Agent tidak ditemukan: {$sourceAgent}";
        $metadataMismatch = $end !== null && (
            trim(explode(' ', (string) ($start['tanggal'] ?? ''), 2)[0]) !== trim(explode(' ', (string) ($end['tanggal'] ?? ''), 2)[0])
            || ($start['agent'] ?? '') !== ($end['agent'] ?? '')
            || ($start['jenis_layanan_konsultasi'] ?? '') !== ($end['jenis_layanan_konsultasi'] ?? '')
            || ($start['status'] ?? '') !== ($end['status'] ?? '')
        );
        if ($metadataMismatch) $errors[] = 'Baris mulai dan selesai tidak memiliki metadata yang sama.';
        if ($startTime instanceof Carbon && $endTime instanceof Carbon && $endTime->lt($startTime)) $errors[] = 'Waktu selesai lebih kecil dari waktu mulai.';
        $status = match (Str::lower(trim((string) ($start['status'] ?? '')))) { 'selesai', 'done' => 'completed', 'dialihkan ke l2' => 'dialihkan_ke_l2', default => null };
        if ($status === null) $errors[] = 'Status tidak dikenal.';
        if ($errors !== []) return [null, $errors, $warnings];
        $code = $this->code($startTime); $hash = hash('sha256', json_encode([$startTime->format('c'), $endTime->format('c'), $service, $status, $sourceAgent]));
        return [[
            'nama_lengkap' => 'Data LKPP', 'jabatan' => 'Data Import', 'asal_pt' => 'LKPP', 'jenis_layanan' => $service, 'detail_keperluan' => $service,
            'tanggal_jam' => $startTime, 'tahun' => $startTime->year, 'waktu_mulai_tatap_muka' => $startTime, 'waktu_selesai_tatap_muka' => $endTime, 'kode_reservasi' => $code, 'status' => $status, 'agent_id' => $agentId, 'source' => 'lkpp_gsheet', 'source_agent_name' => $sourceAgent ?: null, 'import_hash' => $hash,
        ], $errors, $warnings];
    }

    private function code(Carbon $dateTime): string
    {
        do { $code = 'RES-' . $dateTime->format('Ymd') . '-' . Str::upper(Str::random(4)); } while (Reservation::where('kode_reservasi', $code)->exists());
        return $code;
    }
}
