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

        // Allow users with roles (manager, supervisor, cashier, storekeeper, kitchen, receptionist)
        if ($user->role && $user->is_active) {
            $routeName = optional($request->route())->getName();

            // Kitchen accounts can only use kitchen pages
            if ($user->isKitchen()) {
                $allowed = $routeName && str_starts_with($routeName, 'admin.kitchen.');

                if (!$allowed) {
                    return redirect()->route('admin.kitchen.index');
                }
            }

            // Receptionists can only use Hotel POS, room bookings, and hotel reports
            if ($user->isReceptionist()) {
                $allowedPrefixes = [
                    'admin.hotel-pos.',
                    'admin.room-bookings.',
                ];
                $allowedRoutes = [
                    'admin.reports.index',
                    'admin.reports.hotel-bookings',
                    'admin.reports.export.hotel-bookings',
                ];

                $allowed = ($routeName && (
                    collect($allowedPrefixes)->contains(fn ($prefix) => str_starts_with($routeName, $prefix))
                    || in_array($routeName, $allowedRoutes, true)
                ));

                if (!$allowed) {
                    return redirect()->route('admin.hotel-pos.index');
                }
            }

            return $next($request);
        }

        return redirect('/login')->withErrors([
            'email' => 'You do not have permission to access this area.',
        ]);
    }
}
