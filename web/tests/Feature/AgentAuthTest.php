<?php

use App\Models\Agent;
use App\Models\Pimpinan;
use App\Models\Superadmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('agent login page can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Masuk ke Portal Internal TemuLKPP.');
});

test('agent can login and is redirected to dashboard', function () {
    $agent = Agent::create([
        'name' => 'Agent 1',
        'email' => 'agent.auth@temulkpp.com',
        'password' => 'Agent@12345',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $agent->email,
        'password' => 'Agent@12345',
    ]);

    $response->assertRedirect(route('agent.dashboard'));
    $this->assertAuthenticatedAs($agent, 'agent');
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

test('login page stays open when an actor is already logged in', function () {
    $agent = Agent::create([
        'name' => 'Agent 1',
        'email' => 'agent.login@temulkpp.com',
        'password' => 'Agent@12345',
    ]);

    $this->actingAs($agent, 'agent')
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Sedang login di browser ini');
});

test('pimpinan can open the login page without logging out first', function () {
    $pimpinan = Pimpinan::create([
        'name' => 'Pimpinan 1',
        'email' => 'pimpinan.login@temulkpp.com',
        'password' => 'Pimpinan@12345',
    ]);

    $this->actingAs($pimpinan, 'pimpinan')
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Pimpinan · Pimpinan 1');
});

test('superadmin can open the login page without logging out first', function () {
    $superadmin = Superadmin::create([
        'name' => 'Superadmin',
        'email' => 'superadmin.login@temulkpp.com',
        'password' => 'password',
    ]);

    $this->actingAs($superadmin, 'superadmin')
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Superadmin · Superadmin');
});

test('one browser can be logged in as every actor at once', function () {
    $superadmin = Superadmin::create([
        'name' => 'Superadmin',
        'email' => 'superadmin.multi@temulkpp.com',
        'password' => 'password',
    ]);
    $agent = Agent::create([
        'name' => 'Agent Multi',
        'email' => 'agent.multi@temulkpp.com',
        'password' => 'Agent@12345',
    ]);
    $pimpinan = Pimpinan::create([
        'name' => 'Pimpinan Multi',
        'email' => 'pimpinan.multi@temulkpp.com',
        'password' => 'Pimpinan@12345',
    ]);

    $this->post(route('login.store'), [
        'email' => $superadmin->email,
        'password' => 'password',
    ])->assertRedirect(route('superadmin.dashboard'));

    // Login aktor berikutnya tidak perlu logout aktor sebelumnya.
    $this->post(route('login.store'), [
        'email' => $agent->email,
        'password' => 'Agent@12345',
    ])->assertRedirect(route('agent.dashboard'));

    $this->post(route('login.store'), [
        'email' => $pimpinan->email,
        'password' => 'Pimpinan@12345',
    ])->assertRedirect(route('pimpinan.dashboard'));

    $this->assertAuthenticatedAs($superadmin, 'superadmin');
    $this->assertAuthenticatedAs($agent, 'agent');
    $this->assertAuthenticatedAs($pimpinan, 'pimpinan');
});

test('logout only ends the requested actor session', function () {
    $superadmin = Superadmin::create([
        'name' => 'Superadmin',
        'email' => 'superadmin.logout@temulkpp.com',
        'password' => 'password',
    ]);
    $agent = Agent::create([
        'name' => 'Agent Logout',
        'email' => 'agent.logout@temulkpp.com',
        'password' => 'Agent@12345',
    ]);

    $this->post(route('login.store'), [
        'email' => $superadmin->email,
        'password' => 'password',
    ]);
    $this->post(route('login.store'), [
        'email' => $agent->email,
        'password' => 'Agent@12345',
    ]);

    $this->post(route('logout'), ['guard' => 'agent'])
        ->assertRedirect(route('login'));

    $this->assertGuest('agent');
    $this->assertAuthenticatedAs($superadmin, 'superadmin');
});

test('logout without a guard ends every actor session', function () {
    $superadmin = Superadmin::create([
        'name' => 'Superadmin',
        'email' => 'superadmin.logoutall@temulkpp.com',
        'password' => 'password',
    ]);
    $agent = Agent::create([
        'name' => 'Agent Logout All',
        'email' => 'agent.logoutall@temulkpp.com',
        'password' => 'Agent@12345',
    ]);

    $this->post(route('login.store'), [
        'email' => $superadmin->email,
        'password' => 'password',
    ]);
    $this->post(route('login.store'), [
        'email' => $agent->email,
        'password' => 'Agent@12345',
    ]);

    $this->post(route('logout'))->assertRedirect(route('login'));

    $this->assertGuest('superadmin');
    $this->assertGuest('agent');
});

test('agent pages keep their own sidebar when other actors are logged in', function () {
    $superadmin = Superadmin::create([
        'name' => 'Superadmin Sidebar',
        'email' => 'superadmin.sidebar@temulkpp.com',
        'password' => 'password',
    ]);
    $agent = Agent::create([
        'name' => 'Agent Sidebar',
        'email' => 'agent.sidebar@temulkpp.com',
        'password' => 'Agent@12345',
    ]);

    $this->post(route('login.store'), [
        'email' => $superadmin->email,
        'password' => 'password',
    ]);
    $this->post(route('login.store'), [
        'email' => $agent->email,
        'password' => 'Agent@12345',
    ]);

    $response = $this->get(route('agent.dashboard'));

    $response->assertOk();
    $response->assertSee('whitespace-nowrap">Agent</h1>', false);
    $response->assertDontSee('whitespace-nowrap">Superadmin</h1>', false);
});
