<?php

use App\Models\Agent;
use App\Models\Pimpinan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('superset.url', 'http://superset.test');
    config()->set('superset.internal_url', 'http://superset.test');
    config()->set('superset.admin_username', 'admin');
    config()->set('superset.admin_password', 'admin');
    config()->set('superset.dashboards.agent', 'agent-uuid');
    config()->set('superset.dashboards.pimpinan', 'pimpinan-uuid');
    config()->set('superset.dashboards.superadmin', 'superadmin-uuid');

    Http::fake([
        'superset.test/api/v1/security/login' => Http::response(['access_token' => 'access-123'], 200),
        'superset.test/api/v1/security/guest_token*' => Http::response(['token' => 'guest-abc'], 200),
    ]);
});

test('agent guest token carries row level security clause', function () {
    $agent = Agent::create([
        'name' => 'Agent 7',
        'email' => 'agent.rls7@temulkpp.com',
        'password' => 'Agent@12345',
        'is_active' => true,
    ]);

    $response = $this->actingAs($agent, 'agent')
        ->getJson(route('superset.guest-token', ['dashboardId' => 'agent-uuid']));

    $response->assertOk()->assertJson(['token' => 'guest-abc']);

    Http::assertSent(function ($request) use ($agent) {
        return str_contains($request->url(), 'guest_token')
            && ($request['rls'][0]['clause'] ?? null) === 'agent_id = '.$agent->id;
    });
});

test('pimpinan guest token has no row restriction', function () {
    $pimpinan = Pimpinan::create([
        'name' => 'Pimpinan 1',
        'email' => 'pimpinan.rls1@temulkpp.com',
        'password' => 'Pimpinan@12345',
        'is_active' => true,
    ]);

    $response = $this->actingAs($pimpinan, 'pimpinan')
        ->getJson(route('superset.guest-token', ['dashboardId' => 'pimpinan-uuid']));

    $response->assertOk()->assertJson(['token' => 'guest-abc']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'guest_token')
            && ($request['rls'] ?? null) === [];
    });
});

test('each agent gets own row level security clause', function () {
    $clauses = [];

    foreach (range(1, 3) as $i) {
        $agent = Agent::create([
            'name' => "Agent {$i}",
            'email' => "agent.multi{$i}@temulkpp.com",
            'password' => 'Agent@12345',
            'is_active' => true,
        ]);

        $this->actingAs($agent, 'agent')
            ->getJson(route('superset.guest-token', ['dashboardId' => 'agent-uuid']))
            ->assertOk();

        $clauses[$agent->id] = 'agent_id = '.$agent->id;
    }

    $sent = Http::recorded(function ($request) {
        return str_contains($request->url(), 'guest_token');
    });

    expect($sent)->toHaveCount(3);

    $sentClauses = $sent->map(
        fn ($pair) => $pair[0]['rls'][0]['clause'] ?? null
    )->all();
    sort($sentClauses);

    $expected = array_values($clauses);
    sort($expected);

    // Each agent's token carries exactly its own clause.
    expect($sentClauses)->toBe($expected);

    // No two agents share the same clause: isolation proven.
    expect(count(array_unique(array_values($clauses))))->toBe(3);
});

test('agent cannot request another role dashboard', function () {
    $agent = Agent::create([
        'name' => 'Agent 7',
        'email' => 'agent.rls7@temulkpp.com',
        'password' => 'Agent@12345',
        'is_active' => true,
    ]);

    $this->actingAs($agent, 'agent')
        ->getJson(route('superset.guest-token', ['dashboardId' => 'pimpinan-uuid']))
        ->assertForbidden();
});

test('guest token follows the requested dashboard when several actors are logged in', function () {
    $agent = Agent::create([
        'name' => 'Agent 8',
        'email' => 'agent.rls8@temulkpp.com',
        'password' => 'Agent@12345',
        'is_active' => true,
    ]);
    $pimpinan = Pimpinan::create([
        'name' => 'Pimpinan 2',
        'email' => 'pimpinan.rls2@temulkpp.com',
        'password' => 'Pimpinan@12345',
        'is_active' => true,
    ]);

    $this->actingAs($agent, 'agent');
    $this->actingAs($pimpinan, 'pimpinan');

    $this->getJson(route('superset.guest-token', ['dashboardId' => 'pimpinan-uuid']))
        ->assertOk();

    Http::assertSent(function ($request) use ($pimpinan) {
        return str_contains($request->url(), 'guest_token')
            && ($request['user']['username'] ?? null) === (string) $pimpinan->id
            && ($request['rls'] ?? null) === [];
    });
});
