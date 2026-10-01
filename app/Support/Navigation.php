<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Sidebar / navigation definitions for every authenticated role.
 */
class Navigation
{
    /**
     * Resolve the guard the current visitor is signed in with.
     */
    public static function activeGuard(): ?string
    {
        foreach (['admin', 'clinic', 'doctor', 'patient'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    public static function user(?string $guard = null)
    {
        $guard ??= self::activeGuard();

        return $guard ? Auth::guard($guard)->user() : null;
    }

    /**
     * @return array<int, array{label: string, route: string, icon: string, match: string}>
     */
    public static function items(string $guard): array
    {
        return match ($guard) {
            'admin' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'bi-speedometer2', 'match' => 'admin.dashboard'],
                ['label' => 'Clinics', 'route' => 'admin.clinics.index', 'icon' => 'bi-hospital', 'match' => 'admin.clinics.*'],
                ['label' => 'Doctors', 'route' => 'admin.doctors.index', 'icon' => 'bi-person-badge', 'match' => 'admin.doctors.*'],
                ['label' => 'Patients', 'route' => 'admin.patients.index', 'icon' => 'bi-people', 'match' => 'admin.patients.*'],
                ['label' => 'Appointments', 'route' => 'admin.appointments.index', 'icon' => 'bi-calendar2-check', 'match' => 'admin.appointments.*'],
                ['label' => 'Session Logs', 'route' => 'admin.session-logs.index', 'icon' => 'bi-clock-history', 'match' => 'admin.session-logs.*'],
                ['label' => 'Queries', 'route' => 'admin.queries.index', 'icon' => 'bi-envelope-paper', 'match' => 'admin.queries.*'],
            ],
            'clinic' => [
                ['label' => 'Dashboard', 'route' => 'clinic.dashboard', 'icon' => 'bi-speedometer2', 'match' => 'clinic.dashboard'],
                ['label' => 'Schedules', 'route' => 'clinic.schedules.index', 'icon' => 'bi-calendar3', 'match' => 'clinic.schedules.*'],
                ['label' => 'Appointments', 'route' => 'clinic.appointments.index', 'icon' => 'bi-calendar2-check', 'match' => 'clinic.appointments.*'],
            ],
            'doctor' => [
                ['label' => 'Dashboard', 'route' => 'doctor.dashboard', 'icon' => 'bi-speedometer2', 'match' => 'doctor.dashboard'],
                ['label' => 'Appointment Queue', 'route' => 'doctor.queue', 'icon' => 'bi-list-check', 'match' => 'doctor.queue'],
            ],
            'patient' => [
                ['label' => 'Dashboard', 'route' => 'patient.dashboard', 'icon' => 'bi-speedometer2', 'match' => 'patient.dashboard'],
                ['label' => 'Find a Clinic', 'route' => 'patient.clinics.index', 'icon' => 'bi-search', 'match' => 'patient.clinics.*'],
            ],
            default => [],
        };
    }

    /**
     * Secondary "quick action" links rendered in the top bar.
     *
     * @return array<int, array{label: string, route: string, icon: string}>
     */
    public static function actions(string $guard): array
    {
        return match ($guard) {
            'admin' => [
                ['label' => 'Review clinics', 'route' => 'admin.clinics.index', 'icon' => 'bi-hospital'],
                ['label' => 'Open queries', 'route' => 'admin.queries.index', 'icon' => 'bi-envelope-paper'],
            ],
            'clinic' => [
                ['label' => 'Publish schedule', 'route' => 'clinic.schedules.index', 'icon' => 'bi-calendar-plus'],
                ['label' => 'Today at a glance', 'route' => 'clinic.appointments.index', 'icon' => 'bi-calendar2-check'],
            ],
            'doctor' => [
                ['label' => 'Open queue', 'route' => 'doctor.queue', 'icon' => 'bi-list-check'],
            ],
            'patient' => [
                ['label' => 'Book appointment', 'route' => 'patient.clinics.index', 'icon' => 'bi-calendar-plus'],
            ],
            default => [],
        };
    }

    /**
     * Human readable role label used in the top bar.
     */
    public static function label(string $guard): string
    {
        return match ($guard) {
            'admin' => 'Administrator',
            'clinic' => 'Clinic',
            'doctor' => 'Doctor',
            'patient' => 'Patient',
            default => 'Guest',
        };
    }
}
