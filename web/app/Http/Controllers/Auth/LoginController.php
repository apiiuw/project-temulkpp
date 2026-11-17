<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Pimpinan;
use App\Models\Superadmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Guard yang boleh login di portal internal.
     *
     * @var list<string>
     */
    private const GUARDS = ['agent', 'pimpinan', 'superadmin'];

    /** @var array<string, class-string> */
    private const GUARD_MODELS = [
        'agent' => Agent::class,
        'pimpinan' => Pimpinan::class,
        'superadmin' => Superadmin::class,
    ];

    public function create(Request $request): View
    {
        return view('auth.login', [
            'title' => 'Login Internal',
            'activeSessions' => $this->activeSessions($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        foreach (self::GUARDS as $guard) {
            $provider = Auth::guard($guard)->getProvider();
            $user = $provider->retrieveByCredentials(['email' => $credentials['email']]);

            if (! $user || ! $provider->validateCredentials($user, ['password' => $credentials['password']])) {
                continue;
            }

            // Aktor non-aktif tidak boleh masuk (Superadmin selalu aktif).
            if ($guard !== 'superadmin' && ! $user->is_active) {
                return back()->with('error_inactive', sprintf(
                    'Akun %s Anda telah dinonaktifkan oleh Superadmin.',
                    Str::ucfirst($guard)
                ));
            }

            $tabId = $this->resolveLoginTabId($request);

            // Simpan akun per-tab agar banyak akun (termasuk role sama) bisa aktif di tab terpisah.
            $request->session()->put("tab_auth.{$tabId}", [
                $guard => $user->getAuthIdentifier(),
            ]);

            $request->session()->regenerate();
            URL::defaults(['_tab' => $tabId]);

            return redirect()->route("{$guard}.dashboard", ['_tab' => $tabId]);
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password tidak sesuai dengan akun yang terdaftar.',
        ]);
    }

    /**
     * Logout hanya tab/aktor yang diminta. Session tab lain tetap utuh,
     * kecuali tanpa parameter spesifik (logout semua).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $tabId = $this->validTabId($request->input('_tab') ?? $request->query('_tab'));
        $requestedGuard = $request->input('guard');
        $singleGuard = is_string($requestedGuard) && in_array($requestedGuard, self::GUARDS, true)
            ? $requestedGuard
            : null;

        if ($tabId !== null) {
            if ($singleGuard !== null) {
                $request->session()->forget("tab_auth.{$tabId}.{$singleGuard}");
                if (empty($request->session()->get("tab_auth.{$tabId}", []))) {
                    $request->session()->forget("tab_auth.{$tabId}");
                }
            } else {
                $request->session()->forget("tab_auth.{$tabId}");
            }

            $request->session()->regenerateToken();
            URL::defaults([]);

            return redirect()->route('login');
        }

        foreach ($singleGuard !== null ? [$singleGuard] : self::GUARDS as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        if ($singleGuard !== null) {
            $request->session()->regenerateToken();
        } else {
            $request->session()->forget('tab_auth');
            $request->session()->invalidate();
        }

        URL::defaults([]);

        return redirect()->route('login');
    }

    /**
     * Daftar sesi akun yang sedang login di browser ini (per tab + legacy guard).
     *
     * @return list<array{tab_id: string|null, guard: string, label: string, name: string, email: string, dashboard: string}>
     */
    private function activeSessions(Request $request): array
    {
        $sessions = [];
        $tabSessions = $request->session()->get('tab_auth', []);

        if (is_array($tabSessions)) {
            foreach ($tabSessions as $tabId => $guards) {
                if (! is_string($tabId) || ! is_array($guards)) {
                    continue;
                }

                foreach ($guards as $guard => $userId) {
                    $modelClass = self::GUARD_MODELS[$guard] ?? null;
                    if (! $modelClass || ! $userId) {
                        continue;
                    }

                    $user = $modelClass::query()->find($userId);
                    if (! $user) {
                        continue;
                    }

                    $sessions[] = [
                        'tab_id' => $tabId,
                        'guard' => $guard,
                        'label' => Str::ucfirst($guard),
                        'name' => (string) $user->name,
                        'email' => (string) $user->email,
                        'dashboard' => route("{$guard}.dashboard", ['_tab' => $tabId]),
                    ];
                }
            }
        }

        if ($sessions === []) {
            foreach (self::GUARDS as $guard) {
                $user = Auth::guard($guard)->user();
                if (! $user) {
                    continue;
                }

                $sessions[] = [
                    'tab_id' => null,
                    'guard' => $guard,
                    'label' => Str::ucfirst($guard),
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                    'dashboard' => route("{$guard}.dashboard"),
                ];
            }
        }

        return $sessions;
    }

    private function resolveLoginTabId(Request $request): string
    {
        $provided = $this->validTabId($request->input('_tab') ?? $request->query('_tab'));

        return $provided ?? Str::lower(Str::random(10));
    }

    private function validTabId(mixed $raw): ?string
    {
        if (! is_string($raw) || ! preg_match('/^[A-Za-z0-9_-]{4,32}$/', $raw)) {
            return null;
        }

        return $raw;
    }
}
