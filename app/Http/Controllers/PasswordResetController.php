<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

/**
 * Password reset for all four role guards (admin, clinic, doctor, patient).
 *
 * Each guard has its own password broker but they share one token table, so a
 * single controller drives the whole flow instead of four near-identical ones.
 */
class PasswordResetController extends Controller
{
    /**
     * Guard name => password broker name.
     *
     * @var array<string, string>
     */
    protected const BROKERS = [
        'admin' => 'admins',
        'clinic' => 'clinics',
        'doctor' => 'doctors',
        'patient' => 'patients',
    ];

    /* ---------------------------------------------------------------------
     | Request the reset link
     | ------------------------------------------------------------------- */

    public function showRequestForm(string $role): View
    {
        return view('auth.forgot-password', [
            'role' => $role,
            'meta' => AuthController::roles()[$role],
        ]);
    }

    public function sendLink(ForgotPasswordRequest $request, string $role): RedirectResponse
    {
        try {
            $status = $this->broker($role)->sendResetLink([
                'email' => $request->string('email')->trim()->toString(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'The reset link could not be sent because the mail server rejected the message. '
                        .'Run "php artisan mail:verify" to diagnose the mail configuration.',
                ]);
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'A reset link was sent recently. Please wait a minute before requesting another one.',
                ]);
        }

        // The same wording is used whether or not the address is registered, so
        // this form cannot be used to discover which emails have accounts.
        return back()->with('status', 'If that address belongs to a '
            .strtolower(AuthController::roles()[$role]['label'])
            .' account, a password reset link is on its way.');
    }

    /* ---------------------------------------------------------------------
     | Choose the new password
     | ------------------------------------------------------------------- */

    public function showResetForm(Request $request, string $token, string $role): View
    {
        return view('auth.reset-password', [
            'role' => $role,
            'meta' => AuthController::roles()[$role],
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function reset(ResetPasswordRequest $request, string $role): RedirectResponse
    {
        $status = $this->broker($role)->reset(
            $request->credentials(),
            function (object $user, string $password): void {
                // No `remember_token` column exists on the hospital tables, so
                // rotating it here would fail with an unknown-column error.
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route($role.'.login')
            ->with('success', 'Your password has been reset. Please sign in with your new password.');
    }

    /* ---------------------------------------------------------------------
     | Internals
     | ------------------------------------------------------------------- */

    /**
     * Resolve the password broker for a role, guarding against an unknown
     * role slipping through from a hand-written URL.
     */
    protected function broker(string $role): PasswordBroker
    {
        abort_unless(isset(self::BROKERS[$role]), 404);

        return Password::broker(self::BROKERS[$role]);
    }
}
