<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MobileStaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('app.login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('app.login')->withErrors([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        $allowed = $user->is_admin
            || $user->isManager()
            || $user->isSupervisor()
            || $user->isCashier()
            || $user->isKitchen();

        if (!$allowed) {
            abort(403, 'This mobile app is only for cashiers and kitchen staff.');
        }

        return $next($request);
    }
}
