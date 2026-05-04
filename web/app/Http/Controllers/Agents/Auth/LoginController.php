<?php

namespace App\Http\Controllers\Agents\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check() && preg_match('/^agent\.[1-7]@temulkpp\.com$/', strtolower((string) Auth::user()->email))) {
            return redirect()->route('agent.dashboard');
        }

        return view('roles.agents.auth.login', [
            'title' => 'Login Agent',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! preg_match('/^agent\.[1-7]@temulkpp\.com$/', strtolower($credentials['email']))) {
            throw ValidationException::withMessages([
                'email' => 'Gunakan akun agent yang terdaftar untuk masuk ke halaman ini.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password agent tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('agent.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
