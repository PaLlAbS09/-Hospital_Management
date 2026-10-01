<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks clinics that have not been approved by an administrator.
 *
 * - status = "pending"  -> "Your registration is pending administrative approval."
 * - status = "rejected" -> rejection message
 *
 * In both cases the clinic session is terminated and the clinic is redirected
 * back to the clinic login screen with a flash message.
 */
class ClinicApproval
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = Auth::guard('clinic')->user();

        if (! $clinic instanceof Clinic || $clinic->status === Clinic::STATUS_APPROVED) {
            return $next($request);
        }

        $message = match ($clinic->status) {
            Clinic::STATUS_PENDING => 'Your registration is pending administrative approval.',
            Clinic::STATUS_REJECTED => 'Your clinic registration has been rejected by the administrator.',
            default => 'Your clinic account is not active. Please contact the administrator.',
        };

        Auth::guard('clinic')->logout();
        $request->session()->regenerateToken();

        return redirect()
            ->route('clinic.login')
            ->with('approval_status', $clinic->status)
            ->with('approval_message', $message)
            ->with('error', $message);
    }
}
