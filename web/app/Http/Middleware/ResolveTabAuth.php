<?php

namespace App\Http\Middleware;

use App\Models\Agent;
use App\Models\Pimpinan;
use App\Models\Superadmin;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ResolveTabAuth
{
    /** @var array<string, class-string> */
    private const GUARD_MODELS = [
        'agent' => Agent::class,
        'pimpinan' => Pimpinan::class,
        'superadmin' => Superadmin::class,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $tabId = $this->extractTabId($request);

        if ($tabId === null) {
            $response = $next($request);

        if ($response instanceof RedirectResponse) {
            $targetUrl = $response->getTargetUrl();
            $parsed = parse_url($targetUrl);
            $path = $parsed['path'] ?? '';
            $isSameHost = ! isset($parsed['host']) || $parsed['host'] === $request->getHost();

            if ($isSameHost && $path !== '/auth/login') {
                parse_str($parsed['query'] ?? '', $query);
                if (! array_key_exists('_tab', $query)) {
                    $separator = str_contains($targetUrl, '?') ? '&' : '?';
                    $response->setTargetUrl($targetUrl . $separator . '_tab=' . urlencode($tabId));
                }
            }
        }

        return $response;
        }

        URL::defaults(['_tab' => $tabId]);

        $tabAuth = $request->session()->get("tab_auth.{$tabId}", []);

        if (is_array($tabAuth)) {
            foreach (self::GUARD_MODELS as $guard => $modelClass) {
                $userId = $tabAuth[$guard] ?? null;

                if (! $userId) {
                    continue;
                }

                $user = $modelClass::query()->find($userId);

                if ($user) {
                    Auth::guard($guard)->setUser($user);
                }
            }
        }

        $response = $next($request);

        if ($response instanceof RedirectResponse) {
            $targetUrl = $response->getTargetUrl();
            $parsed = parse_url($targetUrl);
            $path = $parsed['path'] ?? '';
            $isSameHost = ! isset($parsed['host']) || $parsed['host'] === $request->getHost();

            if ($isSameHost && $path !== '/auth/login') {
                parse_str($parsed['query'] ?? '', $query);
                if (! array_key_exists('_tab', $query)) {
                    $separator = str_contains($targetUrl, '?') ? '&' : '?';
                    $response->setTargetUrl($targetUrl . $separator . '_tab=' . urlencode($tabId));
                }
            }
        }

        return $response;
    }

    private function extractTabId(Request $request): ?string
    {
        $raw = $request->query('_tab') ?? $request->input('_tab') ?? $request->header('X-Tab-Id');

        if (! is_string($raw) || ! preg_match('/^[A-Za-z0-9_-]{4,32}$/', $raw)) {
            return null;
        }

        return $raw;
    }
}
