<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Support\Sms\LogSmsGateway;
use App\Support\Sms\SmsGateway;
use App\Support\Sms\TwilioSmsGateway;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsGateway::class, function ($app): SmsGateway {
            return match ((string) config('sms.driver', 'log')) {
                'twilio' => $app->make(TwilioSmsGateway::class),
                default => $app->make(LogSmsGateway::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Render all pagination links with Bootstrap 5 markup.
        Paginator::useBootstrapFive();

        // Build password reset links for whichever role the account belongs to,
        // so a patient link never lands a doctor on the patient reset form.
        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $role = match (true) {
                $notifiable instanceof Admin => 'admin',
                $notifiable instanceof Clinic => 'clinic',
                $notifiable instanceof Doctor => 'doctor',
                $notifiable instanceof Patient => 'patient',
                default => null,
            };

            if ($role === null) {
                return url('/');
            }

            return route($role.'.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
