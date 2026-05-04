<?php

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('reschedule page can find reservation by code', function () {
    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAA',
    ]);

    $response = $this->get(route('atur-ulang-jadwal', [
        'kode_reservasi' => $reservation->kode_reservasi,
    ]));

    $response->assertOk();
    $response->assertSee($reservation->kode_reservasi);
    $response->assertSee($reservation->nama_lengkap);
});

test('reservation cannot be rescheduled to a full slot when 7 reservations already exist', function () {
    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAA',
    ]);

    for ($i = 0; $i < 7; $i++) {
        Reservation::create([
            'nama_lengkap' => 'Existing Guest ' . $i,
            'jabatan' => 'Koordinator',
            'asal_pt' => 'PT Existing',
            'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
            'detail_keperluan' => 'Existing issue',
            'tanggal_jam' => '2026-04-14 08:40:00',
            'kode_reservasi' => 'RES-20260414-AAA' . $i,
        ]);
    }

    $response = $this->from(route('atur-ulang-jadwal', [
        'kode_reservasi' => $reservation->kode_reservasi,
    ]))->put(route('atur-ulang-jadwal.update', $reservation->kode_reservasi), [
        'tanggal_jam' => '2026-04-14 08:40:00',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('tanggal_jam');
});

test('reservation can be rescheduled and gets a new code', function () {
    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAA',
    ]);

    $response = $this->put(route('atur-ulang-jadwal.update', $reservation->kode_reservasi), [
        'tanggal_jam' => '2026-04-14 08:40:00',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseMissing('reservations', [
        'kode_reservasi' => 'RES-20260413-AAAA',
    ]);
    $this->assertDatabaseHas('reservations', [
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'tanggal_jam' => '2026-04-14 08:40:00',
    ]);
});

test('reservation can be cancelled from reschedule page', function () {
    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAA',
    ]);

    $response = $this->delete(route('atur-ulang-jadwal.destroy', $reservation->kode_reservasi));

    $response->assertRedirect(route('atur-ulang-jadwal'));
    $this->assertDatabaseMissing('reservations', [
        'kode_reservasi' => 'RES-20260413-AAAA',
    ]);
});

test('reservation cannot be rescheduled to a past time on the same day', function () {
    Carbon::setTestNow('2026-04-09 12:30:00');

    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-10 08:00:00',
        'kode_reservasi' => 'RES-20260410-AAAA',
    ]);

    $response = $this->from(route('atur-ulang-jadwal', [
        'kode_reservasi' => $reservation->kode_reservasi,
    ]))->put(route('atur-ulang-jadwal.update', $reservation->kode_reservasi), [
        'tanggal_jam' => '2026-04-09 12:20:00',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('tanggal_jam');

    Carbon::setTestNow();
});

test('reservation can be rescheduled to a future time on the same day', function () {
    Carbon::setTestNow('2026-04-09 12:30:00');

    $reservation = Reservation::create([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-10 08:00:00',
        'kode_reservasi' => 'RES-20260410-AAAB',
    ]);

    $response = $this->put(route('atur-ulang-jadwal.update', $reservation->kode_reservasi), [
        'tanggal_jam' => '2026-04-09 13:00:00',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reservations', [
        'tanggal_jam' => '2026-04-09 13:00:00',
    ]);

    Carbon::setTestNow();
});
