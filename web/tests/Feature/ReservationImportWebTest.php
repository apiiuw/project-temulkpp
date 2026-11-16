<?php

declare(strict_types=1);

use App\Models\Agent;
use App\Models\ReservationImportBatch;
use App\Models\Superadmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

function importSuperadmin(): Superadmin
{
    return Superadmin::create([
        'name' => 'Import Admin',
        'email' => 'import-admin@example.com',
        'password' => 'password',
    ]);
}

function importAgent(): Agent
{
    return Agent::create([
        'name' => 'Agent 4',
        'email' => 'agent.import@example.com',
        'password' => 'Agent@12345',
    ]);
}

function importCsv(string $agent = 'Agent 4'): UploadedFile
{
    return UploadedFile::fake()->createWithContent('history.csv', implode(PHP_EOL, [
        'hari,bulan,tahun,tanggal,waktu,jenis_layanan_konsultasi,status,agent',
        "Tuesday,January,2024,1/2/24 8:18,08:18,Kendala/Permasalahan Aplikasi,Selesai,{$agent}",
        "Tuesday,January,2024,1/2/24 8:21,08:21,Kendala/Permasalahan Aplikasi,Selesai,{$agent}",
    ]));
}

test('superadmin can upload and preview valid history batch', function () {
    $admin = importSuperadmin();
    importAgent();

    $response = $this->actingAs($admin, 'superadmin')->post(route('superadmin.reservation-import.validate'), [
        'file' => importCsv(),
    ]);

    $batch = ReservationImportBatch::firstOrFail();
    $response->assertRedirect(route('superadmin.reservation-import.show', $batch));
    expect($batch->status)->toBe('ready');
    expect($batch->valid_rows)->toBe(1);
    expect($batch->invalid_rows)->toBe(0);
});

test('unknown agent blocks history batch import', function () {
    $admin = importSuperadmin();

    $response = $this->actingAs($admin, 'superadmin')->post(route('superadmin.reservation-import.validate'), [
        'file' => importCsv('Agent 99'),
    ]);

    $batch = ReservationImportBatch::firstOrFail();
    $response->assertRedirect(route('superadmin.reservation-import.show', $batch));
    expect($batch->status)->toBe('validated');
    expect($batch->invalid_rows)->toBe(1);

    $this->actingAs($admin, 'superadmin')
        ->post(route('superadmin.reservation-import.import', $batch))
        ->assertSessionHasErrors('import');
});
