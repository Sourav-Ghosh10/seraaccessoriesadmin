<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = trim($user->role);

        // Normalize roles list (support both separate arguments and comma-separated strings)
        $allowedRoles = [];
        foreach ($roles as $role) {
            $split = explode(',', $role);
            foreach ($split as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        // Treat 'Operation' and 'Operations' as equivalent
        $normalizedUserRoles = [$userRole];
        if ($userRole === 'Operation') $normalizedUserRoles[] = 'Operations';
        if ($userRole === 'Operations') $normalizedUserRoles[] = 'Operation';

        $hasAccess = false;
        foreach ($normalizedUserRoles as $ur) {
            if (in_array($ur, $allowedRoles)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
