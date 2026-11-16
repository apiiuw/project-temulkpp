<?php

declare(strict_types=1);

use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lkpp importer maps historical status and agent', function () {
    $path = tempnam(sys_get_temp_dir(), 'lkpp-');

    file_put_contents($path, implode(PHP_EOL, [
        'hari,bulan,tahun,tanggal,waktu,jenis_layanan_konsultasi,status,agent',
        'Tuesday,January,2024,1/2/24 8:18,08:18,Kendala/Permasalahan Aplikasi,Selesai,Agent 4',
        'Tuesday,January,2024,1/2/24 8:21,08:21,Kendala/Permasalahan Aplikasi,Selesai,Agent 4',
    ]));

    $this->artisan('reservations:import-lkpp', [
        'file' => $path,
    ])->assertExitCode(0);

    expect(Reservation::where('source', 'lkpp_gsheet')->count())->toBe(1);
    expect(Reservation::where('status', 'completed')->count())->toBe(1);
    expect(Reservation::where('status', 'dialihkan_ke_l2')->count())->toBe(0);
    expect(Reservation::where('source_agent_name', 'Agent 4')->value('agent_id'))->not->toBeNull();
    expect(Reservation::where('source', 'lkpp_gsheet')->pluck('kode_reservasi')->every(
        fn (string $code): bool => preg_match('/^RES-\d{8}-[A-Z0-9]{4}$/', $code) === 1,
    ))->toBeTrue();

    unlink($path);
});

test('lkpp importer dry run does not insert rows', function () {
    $path = tempnam(sys_get_temp_dir(), 'lkpp-');

    file_put_contents($path, implode(PHP_EOL, [
        'hari,bulan,tahun,tanggal,waktu,jenis_layanan_konsultasi,status,agent',
        'Tuesday,January,2024,1/2/24 8:18,08:18,Kendala/Permasalahan Aplikasi,Selesai,Agent 4',
        'Tuesday,January,2024,1/2/24 8:21,08:21,Kendala/Permasalahan Aplikasi,Selesai,Agent 4',
    ]));

    $this->artisan('reservations:import-lkpp', [
        'file' => $path,
        '--dry-run' => true,
    ])->assertExitCode(0);

    expect(Reservation::where('source', 'lkpp_gsheet')->count())->toBe(0);

    unlink($path);
});
