<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    /**
     * Get a guest token for Superset dashboard embedding.
     *
     * Single-dashboard-per-role pattern: every Agent shares the Agent
     * dashboard template. Row isolation comes from the guest-token RLS
     * clause (agent_id = <id>), so no per-agent dashboard is needed.
     */
    public function getGuestToken(Request $request)
    {
        $supersetUrl = config('superset.internal_url');
        $username = config('superset.admin_username');
        $password = config('superset.admin_password');
        $dashboardId = $request->query('dashboardId', $request->input('dashboardId'));
        $dashboardId = is_string($dashboardId) ? $dashboardId : null;

        if (! $dashboardId) {
            return response()->json(['error' => 'Dashboard ID is required'], 400);
        }

        [$role, $user] = $this->authenticatedRoleUser($dashboardId);

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $expectedDashboard = config("superset.dashboards.{$role}");

        if (! $expectedDashboard || $dashboardId !== $expectedDashboard) {
            return response()->json(['error' => 'Dashboard not allowed for this role'], 403);
        }

        try {
            // 1. Login to Superset to get access token
            $loginResponse = Http::post("{$supersetUrl}/api/v1/security/login", [
                'username' => $username,
                'password' => $password,
                'provider' => 'db',
                'refresh' => true,
            ]);

            if ($loginResponse->failed()) {
                Log::error('Superset Login Failed', ['response' => $loginResponse->body()]);

                return response()->json(['error' => 'Failed to login to Superset'], 500);
            }

            $accessToken = $loginResponse->json()['access_token'];

            // 2. Get Guest Token (RLS isolates Agent rows; others see global)
            // Klausul "Jenis Data": sinkron dengan kontrol di Laravel — grafik Superset
            // ikut terfilter (Data Uji = >= Okt 2026; Historis = sebelumnya).
            // Klausul di-scope per-dataset HANYA ke dataset reservasi yang punya
            // kolom tanggal_jam (ds1 fisik; ds4 & ds5 virtual). Dataset tanpa
            // tanggal_jam (ds2 agents, ds3 virtual_roles) tidak boleh kena filter
            // agar tak error "Unknown column 'tanggal_jam' in 'where clause'".
            $jenisUji = $request->query('data') === 'uji';
            $jenisClause = $jenisUji
                ? "tanggal_jam >= '2026-10-01'"
                : "(tanggal_jam < '2026-10-01' OR tanggal_jam IS NULL)";

            $rls = [];
            if ($role === 'agent') {
                // Isolasi baris per agent, PLUS filter Jenis Data (sama seperti
                // superadmin/pimpinan) supaya grafik performa agent ikut terfilter.
                $rls[] = [
                    // Cast to int: prevents clause injection.
                    'clause' => 'agent_id = '.(int) $user->id,
                ];

                foreach ([1, 4, 5] as $reservationDatasetId) {
                    $rls[] = [
                        'clause' => $jenisClause,
                        'dataset' => $reservationDatasetId,
                    ];
                }
            } elseif (in_array($role, ['superadmin', 'pimpinan'], true)) {
                foreach ([1, 4, 5] as $reservationDatasetId) {
                    $rls[] = [
                        'clause' => $jenisClause,
                        'dataset' => $reservationDatasetId,
                    ];
                }
            }

            $guestTokenResponse = Http::withToken($accessToken)->post("{$supersetUrl}/api/v1/security/guest_token/", [
                'user' => [
                    'username' => (string) $user->id,
                    'first_name' => $user->name ?? 'Agent',
                    'last_name' => 'User',
                ],
                'resources' => [
                    [
                        'type' => 'dashboard',
                        'id' => $dashboardId,
                    ],
                ],
                'rls' => $rls,
            ]);

            if ($guestTokenResponse->failed()) {
                Log::error('Superset Guest Token Failed', [
                    'status' => $guestTokenResponse->status(),
                    'response' => $guestTokenResponse->json(),
                ]);

                return response()->json([
                    'error' => 'Failed to get guest token',
                    'details' => $guestTokenResponse->json(),
                ], $guestTokenResponse->status());
            }

            return response()->json([
                'token' => $guestTokenResponse->json()['token'],
            ]);
        } catch (\Exception $e) {
            Log::error('Superset Integration Error', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Menentukan aktor (role + user) dari dashboard yang diminta.
     *
     * Karena satu browser boleh login beberapa aktor sekaligus, kita tidak
     * boleh asal mengambil guard pertama yang login: harus guard yang
     * dashboard-nya cocok dengan permintaan, supaya RLS/user tidak salah.
     *
     * @return array{0: string|null, 1: \App\Models\Agent|\App\Models\Pimpinan|\App\Models\Superadmin|null}
     */
    private function authenticatedRoleUser(string $dashboardId): array
    {
        $guards = ['agent', 'pimpinan', 'superadmin'];

        // Utamakan aktor yang dashboard-nya sama dengan yang diminta.
        foreach ($guards as $role) {
            if (auth($role)->check() && config("superset.dashboards.{$role}") === $dashboardId) {
                return [$role, auth($role)->user()];
            }
        }

        // Fallback: aktor pertama yang login (perilaku lama).
        foreach ($guards as $role) {
            $user = auth($role)->user();

            if ($user) {
                return [$role, $user];
            }
        }

        return [null, null];
    }
}
