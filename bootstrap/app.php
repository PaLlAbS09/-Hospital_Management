<?php

use App\Http\Middleware\ClinicApproval;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'clinic.approval' => ClinicApproval::class,
            'guest' => RedirectIfAuthenticated::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            // Patients clicking the "Rate your visit" email link while logged out
            // must land on the patient login (with ?next= back) instead of home,
            // otherwise they see a confusing page and the review URL looks broken.
            if ($request->is('patient/*')) {
                return route('patient.login');
            }

            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
