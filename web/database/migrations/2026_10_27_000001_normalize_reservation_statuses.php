<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data historis hasil import Excel (source = 'EXCEL_IMPORT') menyimpan
     * status dalam Bahasa Indonesia, sedangkan aplikasi memakai kode
     * canonical ('completed', 'dialihkan_ke_l2'). Akibatnya kartu & query
     * count (where status = 'completed') tidak menghitung 1204 baris
     * historis, dan filter dropdown tidak menemukannya.
     *
     * Mapping (dipakai juga oleh LkppReservationImportService):
     *   'Selesai'                   -> 'completed'
     *   'Dialihkan ke L2/Tim Teknis'-> 'dialihkan_ke_l2'
     *
     * Catatan: 3 baris status 'nan' (sampah hasil import) dibiarkan apa
     * adanya — tidak masuk hitungan selesai maupun berjalan.
     */
    public function up(): void
    {
        DB::table('reservations')
            ->where('status', 'Selesai')
            ->update(['status' => 'completed']);

        DB::table('reservations')
            ->where('status', 'Dialihkan ke L2/Tim Teknis')
            ->update(['status' => 'dialihkan_ke_l2']);
    }

    public function down(): void
    {
        // Karena 'completed'/'dialihkan_ke_l2' juga dipakai data asli
        // (bukan cuma hasil normalisasi), status tidak dapat dikembalikan
        // per-baris secara deterministik setelah normalisasi.
    }
};
