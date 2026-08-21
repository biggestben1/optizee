<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user && $user->isKitchen()) {
            return redirect()->intended(route('admin.kitchen.index'));
        }
        if ($user && $user->isReceptionist()) {
            return redirect()->intended(route('admin.hotel-pos.index'));
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Handle quick login with code only (no password).
     */
    public function quickLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'login_code' => ['required', 'string', 'size:4', 'regex:/^[0-9]{4}$/'],
        ]);

        $user = \App\Models\User::where('login_code', $request->login_code)->first();

        if (!$user) {
            return back()->withErrors([
                'login_code' => 'Invalid login code.',
            ])->onlyInput('login_code');
        }

        // Check if user has access
        if (!$user->is_admin && !$user->role) {
            return back()->withErrors([
                'login_code' => 'You do not have access to the system.',
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            return back()->withErrors([
                'login_code' => 'Your account has been deactivated.',
            ]);
        }

        // Log the user in
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->isKitchen()) {
            return redirect()->intended(route('admin.kitchen.index'));
        }
        if ($user->isReceptionist()) {
            return redirect()->intended(route('admin.hotel-pos.index'));
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
