<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PenilaianSampleSeeder extends Seeder
{
    /**
     * Membuat 8 data uji coba fitur TemuLKPP dari awal reservasi hingga penilaian.
     * Tepat 4 data memiliki komentar tamu (rating_comment), dan 4 lainnya tanpa komentar.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        // Ambil ID agent yang tersedia atau default 1-4
        $agents = Agent::where('is_active', true)->limit(4)->get();
        if ($agents->isEmpty()) {
            $agents = Agent::limit(4)->get();
        }

        $samples = [
            [
                'kode_reservasi' => 'RES-' . $now->format('Ymd') . '-TEST01',
                'nama_lengkap' => 'Budi Santoso',
                'jabatan' => 'Pejabat Pembuat Komitmen (PPK)',
                'asal_pt' => 'Dinas Pekerjaan Umum Prov. Jawa Barat',
                'jenis_layanan' => 'Konsultasi Katalog Elektronik',
                'detail_keperluan' => 'Konsultasi kendala teknis penayangan produk etalase beton precast pada Katalog Elektronik V6.',
                'catatan_tatap_muka' => 'Telah dipandu verifikasi sertifikasi SNI dan kelengkapan dokumen pendukung. Masalah selesai.',
                'durasi_menit' => 35,
                'ratings' => [
                    'rating_kemudahan' => 5,
                    'rating_efisiensi' => 5,
                    'rating_sikap' => 5,
                    'rating_kejelasan' => 5,
                    'rating_kepuasan' => 5,
                ],
                // Komen 1
                'rating_comment' => 'Pelayanan sangat profesional dan cepat. Penjelasan agent mengenai Katalog V6 sangat jelas dan to-the-point.',
            ],
            [
                'kode_reservasi' => 'RES-' . $now->format('Ymd') . '-TEST02',
                'nama_lengkap' => 'Siti Nurhaliza',
                'jabatan' => 'Staff Pengadaan / Pokja Pemilihan',
                'asal_pt' => 'Biro Pengadaan Barang dan Jasa Kemendikbud',
                'jenis_layanan' => 'Kendala Akun Sikap',
                'detail_keperluan' => 'Sinkronisasi data kualifikasi penyedia antara SIKaP dengan LPSE daerah mengalami timeout.',
                'catatan_tatap_muka' => 'Dilakukan re-sync manual dan pembersihan cache session pada sistem SIKaP terintegrasi.',
                'durasi_menit' => 45,
                'ratings' => [
                    'rating_kemudahan' => 4,
                    'rating_efisiensi' => 4,
                    'rating_sikap' => 5,
                    'rating_kejelasan' => 5,
                    'rating_kepuasan' => 5,
                ],
                // Komen 2
                'rating_comment' => 'Proses verifikasi di meja resepsionis lancar, ruang konsultasi nyaman dan solusinya langsung bisa dipraktikkan.',
            ],
            [
                'kode_reservasi' => 'RES-' . $now->format('Ymd') . '-TEST03',
                'nama_lengkap' => 'Hendra Wijaya',
                'jabatan' => 'Direktur Operasional',
                'asal_pt' => 'PT Mitra Sarana Informatika',
                'jenis_layanan' => 'Reset Password Rekanan',
                'detail_keperluan' => 'Email verifikator perusahaan sudah tidak aktif dan butuh pembaruan data kontak utama SPSE.',
                'catatan_tatap_muka' => 'Identitas direksi telah dicocokkan dengan Akta Notaris terbaru. Akun berhasil dipulihkan.',
                'durasi_menit' => 20,
                'ratings' => [
                    'rating_kemudahan' => 5,
                    'rating_efisiensi' => 4,
                    'rating_sikap' => 4,
                    'rating_kejelasan' => 4,
                    'rating_kepuasan' => 4,
                ],
                // Komen 3
                'rating_comment' => 'Sangat membantu pemulihan akun SPSE kami yang sempat terkunci sebelum tender ditutup.',
            ],
            [
                'kode_reservasi' => 'RES-' . $now->format('Ymd') . '-TEST04',
                'nama_lengkap' => 'Dewi Anggraini',
                'jabatan' => 'Admin Pengadaan',
                'asal_pt' => 'RSUD Al-Ihsan Bandung',
                'jenis_layanan' => 'Konsultasi Regulasi Pengadaan',
                'detail_keperluan' => 'Klarifikasi batasan pengadaan langsung alat kesehatan darurat sesuai Perpres 12/2021.',
                'catatan_tatap_muka' => 'Diberikan lembar matriks regulasi dan rekomendasi pengadaan darurat bencana/kesehatan.',
                'durasi_menit' => 50,
                'ratings' => [
                    'rating_kemudahan' => 4,
                    'rating_efisiensi' => 3,
                    'rating_sikap' => 5,
                    'rating_kejelasan' => 4,
                    'rating_kepuasan' => 4,
                ],
                // Komen 4
                'rating_comment' => 'Sikap agent sangat ramah dan sabar mendengarkan kendala rumah sakit. Sedikit antre saat verifikasi awal.',
            ],
            [
                'kode_reservasi' => 'RES-' . $now->format('Ymd') . '-TEST05',
                'nama_lengkap' => 'Ahmad Fauzi',
                'jabatan' => 'General Manager',
                'asal_pt' => 'PT Karya Cipta Mandiri',
                'jenis_layanan' => 'Kendala/Permasalahan Aplikasi',
                'detail_keperluan' => 'Gagal submit dokumen penawaran teknis pada tender paket konstruksi jembatan.',
                'catatan_tatap_muka' => 'Pemeriksaan file PDF yang melebihi ukuran batas maksimum. Berhasil di-split dan upload ulang.',
                'durasi_menit' => 30,
                'ratings' => [
                    'rating_kemudahan' => 5,
                    'rating_efisiensi' => 5,
                    'rating_sikap' => 5,
                    'rating_kejelasan' => 5,
                    'rating_kepuasan' => 5,
                ],
                'rating_comment' => null, // Tanpa komentar (1)
            ],
            [
                'kode_reservasi' => 'RES-' . $now->format('Ymd') . '-TEST06',
                'nama_lengkap' => 'Rina Kartika',
                'jabatan' => 'Kepala Sub Bagian Keuangan',
                'asal_pt' => 'Dinas Pendidikan Kab. Bogor',
                'jenis_layanan' => 'Perubahan Data',
                'detail_keperluan' => 'Penggantian Nomor Pokok Wajib Pajak (NPWP) instansi dari format 15 digit ke 16 digit.',
                'catatan_tatap_muka' => 'Validasi SK Kemenkeu dan pembaruan profil master instansi pada sistem.',
                'durasi_menit' => 25,
                'ratings' => [
                    'rating_kemudahan' => 4,
                    'rating_efisiensi' => 4,
                    'rating_sikap' => 4,
                    'rating_kejelasan' => 4,
                    'rating_kepuasan' => 4,
                ],
                'rating_comment' => null, // Tanpa komentar (2)
            ],
            [
                'kode_reservasi' => 'RES-' . $now->format('Ymd') . '-TEST07',
                'nama_lengkap' => 'Bambang Trihatmojo',
                'jabatan' => 'Koordinator IT Pokja',
                'asal_pt' => 'Politeknik Negeri Jakarta',
                'jenis_layanan' => 'Kendala Akun Inaproc',
                'detail_keperluan' => 'Akses Single Sign-On (SSO) Inaproc terhambat setelah migrasi server pusat.',
                'catatan_tatap_muka' => 'Reset token integrasi akun dan pengujian login berhasil di hadapan pemohon.',
                'durasi_menit' => 40,
                'ratings' => [
                    'rating_kemudahan' => 3,
                    'rating_efisiensi' => 4,
                    'rating_sikap' => 4,
                    'rating_kejelasan' => 4,
                    'rating_kepuasan' => 4,
                ],
                'rating_comment' => null, // Tanpa komentar (3)
            ],
            [
                'kode_reservasi' => 'RES-' . $now->format('Ymd') . '-TEST08',
                'nama_lengkap' => 'Maya Indraswari',
                'jabatan' => 'Legal Officer',
                'asal_pt' => 'PT Nusantara Sejahtera Abadi',
                'jenis_layanan' => 'Konsultasi Pengadaan Langsung',
                'detail_keperluan' => 'Konsultasi syarat kualifikasi penyedia jasa konsultansi non-konstruksi nilai di bawah 100 juta.',
                'catatan_tatap_muka' => 'Diberikan checklist kelengkapan izin usaha berbasis risiko (NIB OSS RBA).',
                'durasi_menit' => 30,
                'ratings' => [
                    'rating_kemudahan' => 5,
                    'rating_efisiensi' => 5,
                    'rating_sikap' => 5,
                    'rating_kejelasan' => 5,
                    'rating_kepuasan' => 5,
                ],
                'rating_comment' => null, // Tanpa komentar (4)
            ],
        ];

        foreach ($samples as $index => $item) {
            $agent = $agents->isNotEmpty() ? $agents[$index % $agents->count()] : null;

            // Runtutan waktu hari ini yang realistis
            $baseHour = 8 + ($index * 1); // rentang jam 08:00 - 16:00
            $scheduledAt = Carbon::parse("{$today} {$baseHour}:00:00");
            $checkInAt   = $scheduledAt->copy()->subMinutes(10);
            $startAt     = $scheduledAt->copy()->addMinutes(2);
            $finishAt    = $startAt->copy()->addMinutes($item['durasi_menit']);
            $ratedAt     = $finishAt->copy()->addMinutes(5);

            Reservation::updateOrCreate(
                ['kode_reservasi' => $item['kode_reservasi']],
                [
                    'nama_lengkap' => $item['nama_lengkap'],
                    'jabatan' => $item['jabatan'],
                    'asal_pt' => $item['asal_pt'],
                    'jenis_layanan' => $item['jenis_layanan'],
                    'detail_keperluan' => $item['detail_keperluan'],
                    'tanggal_jam' => $scheduledAt,
                    'tahun' => (int) $scheduledAt->year,
                    'status' => 'completed',
                    'checked_in_at' => $checkInAt,
                    'agent_id' => $agent?->id,
                    'source_agent_name' => $agent?->name,
                    'source' => 'system_test',
                    'waktu_mulai_tatap_muka' => $startAt,
                    'waktu_selesai_tatap_muka' => $finishAt,
                    'catatan_tatap_muka' => $item['catatan_tatap_muka'],
                    'rating_kemudahan' => $item['ratings']['rating_kemudahan'],
                    'rating_efisiensi' => $item['ratings']['rating_efisiensi'],
                    'rating_sikap' => $item['ratings']['rating_sikap'],
                    'rating_kejelasan' => $item['ratings']['rating_kejelasan'],
                    'rating_kepuasan' => $item['ratings']['rating_kepuasan'],
                    'rating_comment' => $item['rating_comment'],
                    'rated_at' => $ratedAt,
                ]
            );
        }
    }
}
