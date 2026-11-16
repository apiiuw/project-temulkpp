<?php

declare(strict_types=1);

use App\Models\Pimpinan;
use App\Models\Superadmin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authenticatedPimpinan(): Pimpinan
{
    $pimpinan = Pimpinan::create([
        'name' => 'Pimpinan Test',
        'email' => 'pimpinan.pages@temulkpp.com',
        'password' => 'Pimpinan@12345',
        'is_active' => true,
    ]);

    test()->actingAs($pimpinan, 'pimpinan');

    return $pimpinan;
}

test('pimpinan pages render successfully', function (string $routeName): void {
    authenticatedPimpinan();

    $this->get(route($routeName))->assertOk();
})->with([
    'pimpinan.dashboard',
    'pimpinan.layanan-selesai',
    'pimpinan.layanan-berjalan',
    'pimpinan.performa-agent',
    'pimpinan.laporan',
]);

test('pimpinan filters reject invalid input', function (): void {
    authenticatedPimpinan();

    $this->get(route('pimpinan.laporan', [
        'status' => 'invalid-status',
        'date_from' => '2026-02-20',
        'date_to' => '2026-02-01',
    ]))->assertSessionHasErrors(['status', 'date_to']);
});

test('pimpinan can download report pdf', function (): void {
    authenticatedPimpinan();

    $response = $this->get(route('pimpinan.laporan.cetak'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('superadmin can download report pdf', function (): void {
    $superadmin = Superadmin::create([
        'name' => 'Superadmin Test',
        'email' => 'superadmin.report@temulkpp.com',
        'password' => 'Superadmin@12345',
        'is_active' => true,
    ]);

    $this->actingAs($superadmin, 'superadmin');

    $response = $this->get(route('superadmin.laporan.cetak'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
