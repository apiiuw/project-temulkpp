<?php

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function jakartaNow(int $year, int $month, int $day, int $hour, int $minute, int $second = 0): Carbon
{
    return Carbon::create($year, $month, $day, $hour, $minute, $second, 'Asia/Jakarta');
}

test('front desk page can display reservation by code', function () {
    Carbon::setTestNow(jakartaNow(2026, 4, 13, 7, 45));

    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAA',
        'status' => 'pending',
    ]);

    $response = $this->get(route('front-desk', [
        'kode_reservasi' => $reservation->kode_reservasi,
    ]));

    $response->assertOk();
    $response->assertSee($reservation->kode_reservasi);
    $response->assertSee('Reservasi ini belum dikonfirmasi hadir oleh front desk.');

    Carbon::setTestNow();
});

test('front desk can confirm guest arrival', function () {
    Carbon::setTestNow(jakartaNow(2026, 4, 13, 7, 45));

    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAA',
        'status' => 'pending',
    ]);

    $response = $this->patch(route('front-desk.confirm', $reservation->kode_reservasi));

    $response->assertRedirect(route('front-desk', [
        'kode_reservasi' => $reservation->kode_reservasi,
    ]));
    $this->assertDatabaseHas('reservations', [
        'kode_reservasi' => $reservation->kode_reservasi,
        'status' => 'checked_in_front_desk',
    ]);

    Carbon::setTestNow();
});

test('front desk can still confirm guest arrival within 15 minutes of lateness', function () {
    Carbon::setTestNow(jakartaNow(2026, 4, 13, 8, 10));

    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAD',
        'status' => 'pending',
    ]);

    $response = $this->patch(route('front-desk.confirm', $reservation->kode_reservasi));

    $response->assertRedirect(route('front-desk', [
        'kode_reservasi' => $reservation->kode_reservasi,
    ]));
    $this->assertDatabaseHas('reservations', [
        'kode_reservasi' => $reservation->kode_reservasi,
        'status' => 'checked_in_front_desk',
    ]);

    Carbon::setTestNow();
});

test('front desk rejects confirmation when reservation is not on the same day', function () {
    Carbon::setTestNow(jakartaNow(2026, 4, 12, 8, 0));

    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAB',
        'status' => 'pending',
    ]);

    $response = $this->followingRedirects()->patch(route('front-desk.confirm', $reservation->kode_reservasi));

    $response->assertOk();
    $response->assertSee('Konfirmasi Ditolak');
    $response->assertSee('Konfirmasi kedatangan hanya bisa dilakukan pada hari yang sama dengan tanggal reservasi.');
    $this->assertDatabaseHas('reservations', [
        'kode_reservasi' => $reservation->kode_reservasi,
        'status' => 'pending',
    ]);

    Carbon::setTestNow();
});

test('front desk rejects confirmation when reservation is more than 15 minutes late', function () {
    Carbon::setTestNow(jakartaNow(2026, 4, 13, 8, 16));

    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAC',
        'status' => 'pending',
    ]);

    $response = $this->followingRedirects()->patch(route('front-desk.confirm', $reservation->kode_reservasi));

    $response->assertOk();
    $response->assertSee('Konfirmasi Ditolak');
    $this->assertDatabaseHas('reservations', [
        'kode_reservasi' => $reservation->kode_reservasi,
        'status' => 'expired_front_desk',
    ]);

    Carbon::setTestNow();
});

test('front desk marks reservation as expired when scanned after 15 minute tolerance', function () {
    Carbon::setTestNow(jakartaNow(2026, 4, 13, 8, 16));

    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAC',
        'status' => 'pending',
    ]);

    $response = $this->get(route('front-desk', [
        'kode_reservasi' => $reservation->kode_reservasi,
    ]));

    $response->assertOk();
    $response->assertSee($reservation->kode_reservasi);
    $this->assertDatabaseHas('reservations', [
        'kode_reservasi' => $reservation->kode_reservasi,
        'status' => 'expired_front_desk',
    ]);

    Carbon::setTestNow();
});
