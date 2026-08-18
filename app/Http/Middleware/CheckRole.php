<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Admin has access to everything
        if ($user->is_admin) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if ($user->role && in_array($user->role->name, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}











