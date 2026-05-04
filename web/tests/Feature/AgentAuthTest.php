<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('agent login page can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Masuk ke akun agent');
});

test('agent can login and is redirected to dashboard', function () {
    $agent = User::factory()->create([
        'name' => 'Agent 1',
        'email' => 'agent.1@temulkpp.com',
        'password' => 'Agent@12345',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $agent->email,
        'password' => 'Agent@12345',
    ]);

    $response->assertRedirect(route('agent.dashboard'));
    $this->assertAuthenticatedAs($agent);
});

test('non agent account cannot login from agent form', function () {
    User::factory()->create([
        'name' => 'Regular User',
        'email' => 'user@example.com',
        'password' => 'Agent@12345',
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'user@example.com',
        'password' => 'Agent@12345',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('agent dashboard requires authenticated agent account', function () {
    $response = $this->get(route('agent.dashboard'));

    $response->assertRedirect(route('login'));
});
