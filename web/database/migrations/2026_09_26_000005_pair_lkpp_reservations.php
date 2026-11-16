<?php

declare(strict_types=1);

use App\Models\Reservation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $rows = Reservation::query()
            ->where('source', 'lkpp_gsheet')
            ->orderBy('source_row')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $pairs = [];
        for ($index = 0; $index < $rows->count(); $index += 2) {
            $start = $rows[$index];
            $end = $rows[$index + 1] ?? null;
            $sameMetadata = $end !== null
                && $start->source_agent_name === $end->source_agent_name
                && $start->jenis_layanan === $end->jenis_layanan
                && $start->status === $end->status
                && $start->tanggal_jam->toDateString() === $end->tanggal_jam->toDateString();

            $pairs[] = [$start, $sameMetadata ? $end : null];
        }

        \DB::transaction(function () use ($rows, $pairs): void {
            Reservation::whereIn('id', $rows->pluck('id'))->delete();

            foreach ($pairs as [$start, $end]) {
                $startTime = $start->tanggal_jam;
                $endTime = $end?->tanggal_jam;
                $code = $this->code($startTime);
                Reservation::create([
                    'nama_lengkap' => $start->nama_lengkap,
                    'jabatan' => $start->jabatan,
                    'asal_pt' => $start->asal_pt,
                    'jenis_layanan' => $start->jenis_layanan,
                    'detail_keperluan' => $start->detail_keperluan,
                    'tanggal_jam' => $startTime,
                    'tahun' => $startTime->year,
                    'waktu_mulai_tatap_muka' => $startTime,
                    'waktu_selesai_tatap_muka' => $endTime,
                    'kode_reservasi' => $code,
                    'status' => $start->status,
                    'agent_id' => $start->agent_id,
                    'source' => $start->source,
                    'source_agent_name' => $start->source_agent_name,
                    'source_row' => $start->source_row,
                    'import_hash' => hash('sha256', $start->import_hash . '|' . ($end?->import_hash ?? '')),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Original row-level records cannot be restored without source file reimport.
    }

    private function code(\Carbon\Carbon $dateTime): string
    {
        do {
            $code = 'RES-' . $dateTime->format('Ymd') . '-' . Str::upper(Str::random(4));
        } while (Reservation::where('kode_reservasi', $code)->exists());

        return $code;
    }
};
