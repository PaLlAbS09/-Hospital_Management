<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route group to a single authentication guard / user role.
 *
 * Usage: ->middleware(['auth:clinic', 'role:clinic'])
 */
class RoleMiddleware
{
    /** Roles that map 1:1 to a configured authentication guard. */
    public const ROLES = ['admin', 'clinic', 'doctor', 'patient'];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $role = strtolower($role);

        if (! in_array($role, self::ROLES, true)) {
            abort(500, "Unknown role guard [{$role}].");
        }

        if (! Auth::guard($role)->check()) {
            return redirect()
                ->guest(route($role.'.login'))
                ->with('error', 'Please sign in to continue.');
        }

        // Make the role guard the default for this request so that
        // `$request->user()`, `auth()->user()` and `Auth::user()` all resolve
        // to the authenticated user of this role.
        Auth::shouldUse($role);

        $request->session()->put('active_guard', $role);

        return $next($request);
    }
}
