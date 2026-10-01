<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Multi-guard replacement for the framework "guest" middleware.
 *
 * Redirects an already authenticated user to the dashboard that belongs to
 * whichever guard they are signed in with. Clinics that are still pending or
 * have been rejected are logged out so they can see the login screen again.
 */
class RedirectIfAuthenticated
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = $guards ?: RoleMiddleware::ROLES;

        foreach ($guards as $guard) {
            if (! Auth::guard($guard)->check()) {
                continue;
            }

            if ($guard === 'clinic') {
                $clinic = Auth::guard('clinic')->user();

                if (! $clinic instanceof Clinic || ! $clinic->is_approved) {
                    Auth::guard('clinic')->logout();
                    $request->session()->regenerateToken();

                    continue;
                }
            }

            return redirect()->route($guard.'.dashboard');
        }

        return $next($request);
    }
}
