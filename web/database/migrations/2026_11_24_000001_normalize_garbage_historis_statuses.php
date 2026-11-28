<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 8 baris historis (source = 'EXCEL_IMPORT') menyimpan status sampah
     * hasil import Excel:
     *
     *   - 7 baris status 'nan'    -> sel status di Excel memang kosong
     *   - 1 baris 'in_progress'   -> sel status di Excel berisi "Proses"
     *
     * Karena kartu & query count memakai `status = 'completed'`, kedua nilai
     * itu dihitung "belum selesai" sehingga kartu historis meleset 8 baris
     * (2394 seharusnya 2402), padahal total historis tetap 2755.
     *
     * Dipetakan ke 'completed' supaya historis konsisten:
     *
     *   Selesai       2394 -> 2402
     *   Tidak Selesai  361 ->  353   (hanya 'dialihkan_ke_l2')
     *   Total         2755 -> 2755   (tidak berubah)
     *
     * Klausa `source = 'EXCEL_IMPORT'` menjamin 14 baris data uji
     * (source 'data-uji'/NULL, tanggal >= 2026-10-01) tidak tersentuh:
     * tidak ada satu pun baris data uji yang ber-source EXCEL_IMPORT.
     *
     * Timestamp sengaja TIDAK diubah (baris historis sudah final).
     */
    public function up(): void
    {
        DB::table('reservations')
            ->where('source', 'EXCEL_IMPORT')
            ->whereNotIn('status', ['completed', 'dialihkan_ke_l2'])
            ->update(['status' => 'completed']);
    }

    public function down(): void
    {
        // Tidak dapat dikembalikan per-baris: 'completed' juga dipakai baris
        // historis lain yang memang sah, sehingga 8 baris ini tidak bisa
        // dibedakan lagi setelah dinormalisasi.
    }
};
