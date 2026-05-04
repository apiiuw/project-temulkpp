<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DashboardSeeder extends Seeder
{
    public function run()
    {
        $agent = Agent::first();
        if (!$agent) return;

        // Clear existing to make it clean if needed, or just add more
        // Reservation::truncate();

        $statuses = ['pending', 'checked_in_front_desk', 'in_progress', 'completed'];
        $layanan = ['SPSE', 'Non SPSE'];
        $instansi = ['Universitas Indonesia', 'ITB', 'UGM', 'PT Maju Bersama', 'Pemkot Jakarta'];

        for ($i = 0; $i < 30; $i++) {
            $daysAgo = rand(0, 10);
            $date = Carbon::now()->subDays($daysAgo)->setHour(rand(8, 16))->setMinute(rand(0, 59));
            
            $status = $statuses[array_rand($statuses)];
            $waktuMulai = null;
            $waktuSelesai = null;

            if ($status === 'in_progress' || $status === 'completed') {
                $waktuMulai = $date->copy()->addMinutes(rand(5, 15));
            }
            if ($status === 'completed') {
                $waktuSelesai = $waktuMulai->copy()->addMinutes(rand(15, 60));
            }

            Reservation::create([
                'nama_lengkap' => 'Tamu ' . ($i + 1),
                'jabatan' => 'Staff',
                'asal_pt' => $instansi[array_rand($instansi)],
                'jenis_layanan' => $layanan[array_rand($layanan)],
                'detail_keperluan' => 'Konsultasi teknis pengadaan',
                'tanggal_jam' => $date,
                'kode_reservasi' => 'RSV-' . strtoupper(Str::random(6)),
                'status' => $status,
                'agent_id' => $agent->id,
                'waktu_mulai_tatap_muka' => $waktuMulai,
                'waktu_selesai_tatap_muka' => $waktuSelesai,
                'checked_in_at' => ($status !== 'pending') ? $date->copy()->addMinutes(rand(-5, 5)) : null,
            ]);
        }
    }
}
