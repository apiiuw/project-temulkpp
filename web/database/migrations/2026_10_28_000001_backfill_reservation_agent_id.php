<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data historis hasil import Excel (source = 'EXCEL_IMPORT') menyimpan
     * nama agent pada kolom `source_agent_name` (mis. 'Agent 1') tetapi
     * `agent_id` (FK) dibiarkan NULL. Akibatnya JOIN `reservations ->
     * agents` di dataset virtual ds4 (reservation_agent_analytics) menghasilkan
     * agent_name = 'Tanpa Agent' untuk semua baris historis, dan chart
     * "Kinerja Semua Agent" (yang memakai filter bawaan `agent_name !=
     * 'Tanpa Agent'`) menjadi kosong.
     *
     * Perbaikan: isi `agent_id` dengan mencocokkan `source_agent_name` ke
     * `agents.name`. Nilai `source_agent_name` historis persis sama dengan
     * `agents.name' ('Agent 1'..'Agent 16'), jadi pemetaannya deterministik.
     *
     * 9 baris historis memakai `source_agent_name = 'Agent Cadangan'`.
     * 'Agent Cadangan' adalah sebutan untuk Agent 6 (agent cadangan), jadi
     * baris-baris itu dipetakan ke `agent_id` Agent 6 (dicari by name agar
     * tahan terhadap perubahan id). Dengan demikian Agent 6 tampil di
     * dashboard dengan 9 reservasi cadangan itu.
     *
     * Baris `source = 'data-uji'` tidak disentuh — data-uji sudah memiliki
     * `agent_id` yang benar.
     */
    public function up(): void
    {
        // Isi agent_id dari source_agent_name (hanya baris yang masih NULL).
        DB::update(
            '
            UPDATE reservations r
            JOIN agents a ON a.name = r.source_agent_name
            SET r.agent_id = a.id
            WHERE r.agent_id IS NULL
              AND (r.source IS NULL OR r.source != ?)
              AND r.source_agent_name IS NOT NULL
              AND r.source_agent_name != ?
            ',
            ['data-uji', '']
        );

        // 'Agent Cadangan' adalah Agent 6 (agent cadangan) — petakan 9 baris itu.
        DB::update(
            '
            UPDATE reservations r
            JOIN agents a ON a.name = ?
            SET r.agent_id = a.id
            WHERE r.agent_id IS NULL
              AND r.source_agent_name = ?
              AND (r.source IS NULL OR r.source != ?)
            ',
            ['Agent 6', 'Agent Cadangan', 'data-uji']
        );
    }

    public function down(): void
    {
        // Karena agent_id historis sekarang mengikuti source_agent_name,
        // FK tidak dapat dikembalikan ke NULL secara deterministik per-baris
        // (beberapa baris data-uji memang ber-NULL agent_id secara sah).
    }
};
