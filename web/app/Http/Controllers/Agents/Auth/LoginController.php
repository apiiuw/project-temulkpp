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
        if (Auth::guard('agent')->check()) {
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

        if (! Auth::guard('agent')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password agent tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('agent.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('agent')->logout();

        // Jangan invalidate seluruh session: aktor lain yang sedang login
        // di browser yang sama harus tetap utuh.
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
