<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    /**
     * Get a guest token for Superset dashboard embedding.
     */
    public function getGuestToken(Request $request)
    {
        $supersetUrl = env('SUPERSET_URL', 'http://localhost:8088');
        $username = env('SUPERSET_ADMIN_USERNAME', 'admin');
        $password = env('SUPERSET_ADMIN_PASSWORD', 'admin');
        $dashboardId = $request->input('dashboardId');

        if (!$dashboardId) {
            return response()->json(['error' => 'Dashboard ID is required'], 400);
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

            $user = auth()->user() ?? auth()->guard('pimpinan')->user() ?? auth()->guard('superadmin')->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            // 2. Get Guest Token
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
                'rls' => $user instanceof \App\Models\Agent ? [
                    [
                        'clause' => 'agent_id = ' . $user->id
                    ]
                ] : []
            ]);

            if ($guestTokenResponse->failed()) {
                Log::error('Superset Guest Token Failed', [
                    'status' => $guestTokenResponse->status(),
                    'response' => $guestTokenResponse->json()
                ]);
                return response()->json([
                    'error' => 'Failed to get guest token',
                    'details' => $guestTokenResponse->json()
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
}
