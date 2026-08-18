<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Allow access to admin users OR users with specific roles
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors([
                'email' => 'Please login to continue.',
            ]);
        }

        $user = Auth::user();

        // Allow admin users
        if ($user->is_admin) {
            return $next($request);
        }

        // Allow users with roles (manager, supervisor, cashier, storekeeper, kitchen)
        if ($user->role && $user->is_active) {
            return $next($request);
        }

        return redirect('/login')->withErrors([
            'email' => 'You do not have permission to access this area.',
        ]);
    }
}

