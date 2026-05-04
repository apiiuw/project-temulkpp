<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;
use App\Models\ReservationSetting;

class ReservationSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Service Types
        $serviceTypes = [
            'Konsultasi SPSE / Pengadaan Elektronik',
            'Konsultasi Regulasi Pengadaan',
            'Konsultasi Katalog Elektronik',
            'Konsultasi Pengadaan Langsung',
        ];

        foreach ($serviceTypes as $type) {
            ServiceType::firstOrCreate(['name' => $type]);
        }

        // Reservation Settings
        $settings = [
            [
                'key' => 'available_days',
                'value' => json_encode([1, 2, 3, 4]), // Monday to Thursday
                'description' => 'Hari tersedia reservasi (1=Senin, 4=Kamis)',
            ],
            [
                'key' => 'max_reservations_per_slot',
                'value' => '7',
                'description' => 'Maksimal reservasi per slot waktu',
            ],
            [
                'key' => 'consultation_duration_minutes',
                'value' => '40',
                'description' => 'Durasi setiap slot konsultasi (menit)',
            ],
            [
                'key' => 'morning_start',
                'value' => '08:00',
                'description' => 'Jam mulai sesi pagi',
            ],
            [
                'key' => 'morning_end',
                'value' => '11:20',
                'description' => 'Jam selesai sesi pagi',
            ],
            [
                'key' => 'afternoon_start',
                'value' => '13:00',
                'description' => 'Jam mulai sesi siang',
            ],
            [
                'key' => 'afternoon_end',
                'value' => '15:40',
                'description' => 'Jam selesai sesi siang',
            ],
        ];

        foreach ($settings as $setting) {
            ReservationSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
