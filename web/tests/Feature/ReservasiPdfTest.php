<?php

use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('reservation pdf can be downloaded', function () {
    $reservation = Reservation::create([
        'nama_lengkap' => 'Joni Doe',
        'jabatan' => 'Analis Pengadaan',
        'asal_pt' => 'PT Bangun Negeri',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Butuh bantuan proses pengadaan.',
        'tanggal_jam' => '2026-04-07 09:30:00',
        'kode_reservasi' => 'RES-20260407-ABCD',
    ]);

    $response = $this->get(route('reservasi.download', $reservation->kode_reservasi));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});
