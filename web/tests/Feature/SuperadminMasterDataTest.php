<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureMenuPermission;
use App\Models\Agent;
use App\Models\Pimpinan;
use App\Models\Superadmin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authenticatedMasterDataSuperadmin(): Superadmin
{
    $superadmin = Superadmin::create([
        'name' => 'Superadmin Pagination Test',
        'email' => 'superadmin.pagination@temulkpp.com',
        'password' => 'Superadmin@12345',
        'is_active' => true,
    ]);

    test()->actingAs($superadmin, 'superadmin');

    return $superadmin;
}

test('agent master data paginates and preserves filters', function (): void {
    authenticatedMasterDataSuperadmin();

    foreach (range(1, 21) as $index) {
        Agent::create([
            'name' => "Agent {$index}",
            'email' => "agent.pagination.{$index}@temulkpp.com",
            'password' => 'Agent@12345',
            'is_active' => true,
        ]);
    }

    $response = $this
        ->withoutMiddleware(EnsureMenuPermission::class)
        ->get(route('superadmin.master-agent', ['q' => 'Agent', 'page' => 2]));

    $response
        ->assertOk()
        ->assertSee('Menampilkan')
        ->assertSee('Agent 16')
        ->assertDontSee('Agent 21')
        ->assertSee('q=Agent');
});

test('pimpinan master data paginates and preserves filters', function (): void {
    authenticatedMasterDataSuperadmin();

    foreach (range(1, 21) as $index) {
        Pimpinan::create([
            'name' => "Pimpinan {$index}",
            'email' => "pimpinan.pagination.{$index}@temulkpp.com",
            'password' => 'Pimpinan@12345',
            'is_active' => true,
        ]);
    }

    $response = $this
        ->withoutMiddleware(EnsureMenuPermission::class)
        ->get(route('superadmin.master-pimpinan', ['q' => 'Pimpinan', 'page' => 2]));

    $response
        ->assertOk()
        ->assertSee('Menampilkan')
        ->assertSee('Pimpinan 16')
        ->assertDontSee('Pimpinan 21')
        ->assertSee('q=Pimpinan');
});
