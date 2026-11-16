<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgentUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('agent');

        if (! $user || ! $user->is_active) {
            // Hapus sesi agent saja — sesi aktor lain di browser ini tetap utuh.
            auth('agent')->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun ini tidak memiliki akses ke halaman agent.',
            ]);
        }

        return $next($request);
    }
}
