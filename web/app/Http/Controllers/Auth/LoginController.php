<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function create(): View
    {
        return view('auth.login', [
            'title' => 'Login Internal',
            'activeSessions' => $this->activeSessions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        foreach (self::GUARDS as $guard) {
            if (! Auth::guard($guard)->attempt($credentials, $request->boolean('remember'))) {
                continue;
            }

            // Aktor non-aktif tidak boleh masuk (Superadmin selalu aktif).
            if ($guard !== 'superadmin' && ! Auth::guard($guard)->user()->is_active) {
                Auth::guard($guard)->logout();

                return back()->with('error_inactive', sprintf(
                    'Akun %s Anda telah dinonaktifkan oleh Superadmin.',
                    Str::ucfirst($guard)
                ));
            }

            // Regenerate hanya mengganti ID session, data (termasuk login
            // aktor lain yang sudah ada di browser ini) tetap dipertahankan.
            $request->session()->regenerate();

            return redirect()->intended(route("{$guard}.dashboard"));
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password tidak sesuai dengan akun yang terdaftar.',
        ]);
    }

    /**
     * Logout hanya aktor yang diminta. Session aktor lain tetap utuh,
     * kecuali tidak ada guard tertentu (logout semua).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $requested = $request->input('guard');
        $singleGuard = is_string($requested) && in_array($requested, self::GUARDS, true)
            ? $requested
            : null;

        foreach ($singleGuard !== null ? [$singleGuard] : self::GUARDS as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        if ($singleGuard !== null) {
            // Aktor lain yang sedang login di browser ini tetap utuh.
            $request->session()->regenerateToken();
        } else {
            // Tanpa guard spesifik: bersihkan seluruh session (logout semua).
            $request->session()->invalidate();
        }

        return redirect()->route('login');
    }

    /**
     * Daftar aktor yang sedang login di browser ini.
     *
     * @return list<array{guard: string, label: string, name: string, email: string, dashboard: string}>
     */
    private function activeSessions(): array
    {
        $sessions = [];

        foreach (self::GUARDS as $guard) {
            $user = Auth::guard($guard)->user();

            if (! $user) {
                continue;
            }

            $sessions[] = [
                'guard' => $guard,
                'label' => Str::ucfirst($guard),
                'name' => (string) $user->name,
                'email' => (string) $user->email,
                'dashboard' => route("{$guard}.dashboard"),
            ];
        }

        return $sessions;
    }
}
