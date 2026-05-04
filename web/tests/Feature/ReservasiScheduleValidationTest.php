<?php

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function validReservationPayload(array $overrides = []): array
{
    return array_merge([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
    ], $overrides);
}

test('reservation rejects day outside monday to thursday', function () {
    $response = $this->from('/reservasi')->post('/reservasi', validReservationPayload([
        'tanggal_jam' => '2026-04-12 08:00:00',
    ]));

    $response->assertRedirect('/reservasi');
    $response->assertSessionHasErrors('tanggal_jam');
});

test('reservation rejects time outside 40 minute schedule', function () {
    $response = $this->from('/reservasi')->post('/reservasi', validReservationPayload([
        'tanggal_jam' => '2026-04-13 07:15:00',
    ]));

    $response->assertRedirect('/reservasi');
    $response->assertSessionHasErrors('tanggal_jam');
});

test('reservation rejects occupied slot when 7 reservations exist', function () {
    // Create 7 reservations for the same slot
    for ($i = 0; $i < 7; $i++) {
        Reservation::create([
            'nama_lengkap' => 'Existing Guest ' . $i,
            'jabatan' => 'Koordinator',
            'asal_pt' => 'PT Existing',
            'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
            'detail_keperluan' => 'Existing issue',
            'tanggal_jam' => '2026-04-13 08:00:00',
            'kode_reservasi' => 'RES-20260413-AAA' . $i,
        ]);
    }

    $response = $this->from('/reservasi')->post('/reservasi', validReservationPayload());

    $response->assertRedirect('/reservasi');
    $response->assertSessionHasErrors('tanggal_jam');
});

test('reservation rejects past date and time', function () {
    Carbon::setTestNow('2026-04-09 12:30:00');

    $response = $this->from('/reservasi')->post('/reservasi', validReservationPayload([
        'tanggal_jam' => '2026-04-09 12:20:00',
    ]));

    $response->assertRedirect('/reservasi');
    $response->assertSessionHasErrors('tanggal_jam');

    Carbon::setTestNow();
});

test('reservation accepts same day future slot', function () {
    Carbon::setTestNow('2026-04-09 12:30:00');

    $response = $this->from('/reservasi')->post('/reservasi', validReservationPayload([
        'tanggal_jam' => '2026-04-09 13:00:00',
    ]));

    $response->assertRedirect('/reservasi');
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reservations', [
        'tanggal_jam' => '2026-04-09 13:00:00',
    ]);

    Carbon::setTestNow();
});
