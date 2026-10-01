<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ClinicRegistrationRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PatientRegistrationRequest;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSessionLog;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Handles authentication for all four role guards:
 * admin, clinic, doctor and patient.
 */
class AuthController extends Controller
{
    /**
     * Metadata used by the shared auth Blade templates.
     *
     * @return array<string, array<string, string>>
     */
    public static function roles(): array
    {
        return [
            'admin' => [
                'label' => 'Administrator',
                'title' => 'Administrator Sign In',
                'subtitle' => 'Manage clinics, doctors, patients and appointments.',
                'icon' => 'bi-shield-lock',
                'accent' => 'primary',
            ],
            'clinic' => [
                'label' => 'Clinic',
                'title' => 'Clinic Sign In',
                'subtitle' => 'Publish doctor schedules and monitor local appointments.',
                'icon' => 'bi-hospital',
                'accent' => 'success',
            ],
            'doctor' => [
                'label' => 'Doctor',
                'title' => 'Doctor Sign In',
                'subtitle' => 'Review your queue and record prescriptions.',
                'icon' => 'bi-person-badge',
                'accent' => 'info',
            ],
            'patient' => [
                'label' => 'Patient',
                'title' => 'Patient Sign In',
                'subtitle' => 'Book appointments and view your prescriptions.',
                'icon' => 'bi-person-heart',
                'accent' => 'warning',
            ],
        ];
    }

    /* ---------------------------------------------------------------------
     | Login screens
     | ------------------------------------------------------------------- */

    public function showAdminLoginForm(): View
    {
        return $this->loginView('admin');
    }

    public function showClinicLoginForm(): View
    {
        return $this->loginView('clinic');
    }

    public function showDoctorLoginForm(): View
    {
        return $this->loginView('doctor');
    }

    public function showPatientLoginForm(): View
    {
        return $this->loginView('patient');
    }

    /* ---------------------------------------------------------------------
     | Login handlers
     | ------------------------------------------------------------------- */

    public function adminLogin(LoginRequest $request): RedirectResponse
    {
        return $this->attempt($request, 'admin');
    }

    public function clinicLogin(LoginRequest $request): RedirectResponse
    {
        return $this->attempt($request, 'clinic');
    }

    public function doctorLogin(LoginRequest $request): RedirectResponse
    {
        return $this->attempt($request, 'doctor');
    }

    public function patientLogin(LoginRequest $request): RedirectResponse
    {
        return $this->attempt($request, 'patient');
    }

    /* ---------------------------------------------------------------------
     | Registration screens
     | ------------------------------------------------------------------- */

    public function showClinicRegisterForm(): View
    {
        return $this->registerView('clinic');
    }

    public function showPatientRegisterForm(): View
    {
        return $this->registerView('patient');
    }

    /* ---------------------------------------------------------------------
     | Registration handlers
     | ------------------------------------------------------------------- */

    public function registerClinic(ClinicRegistrationRequest $request): RedirectResponse
    {
        Clinic::create([
            'clinic_name' => $request->string('clinic_name')->trim()->toString(),
            'area' => $request->string('area')->trim()->toString(),
            'contact_number' => $request->string('contact_number')->trim()->toString(),
            'email' => $request->string('email')->trim()->lower()->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'status' => Clinic::STATUS_PENDING,
        ]);

        return redirect()
            ->route('clinic.login')
            ->with('approval_status', Clinic::STATUS_PENDING)
            ->with('approval_message', 'Registration submitted. Your registration is pending administrative approval.')
            ->with('success', 'Registration submitted successfully. You will be able to sign in once an administrator approves your clinic.');
    }

    public function registerPatient(PatientRegistrationRequest $request): RedirectResponse
    {
        $patient = Patient::create([
            'first_name' => $request->string('first_name')->trim()->toString(),
            'last_name' => $request->string('last_name')->trim()->toString(),
            'gender' => $request->string('gender')->toString(),
            'email' => $request->string('email')->trim()->lower()->toString(),
            'contact' => $request->string('contact')->trim()->toString(),
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        Auth::guard('patient')->login($patient);
        Auth::shouldUse('patient');
        $request->session()->regenerate();

        return redirect()
            ->route('patient.dashboard')
            ->with('success', 'Welcome to the hospital portal, '.$patient->full_name.'!');
    }

    /* ---------------------------------------------------------------------
     | Logout handlers
     | ------------------------------------------------------------------- */

    public function adminLogout(Request $request): RedirectResponse
    {
        return $this->logoutGuard($request, 'admin');
    }

    public function clinicLogout(Request $request): RedirectResponse
    {
        return $this->logoutGuard($request, 'clinic');
    }

    public function doctorLogout(Request $request): RedirectResponse
    {
        $this->closeDoctorSession($request);

        return $this->logoutGuard($request, 'doctor');
    }

    public function patientLogout(Request $request): RedirectResponse
    {
        return $this->logoutGuard($request, 'patient');
    }

    /* ---------------------------------------------------------------------
     | Internals
     | ------------------------------------------------------------------- */

    protected function loginView(string $role): View
    {
        return view('auth.login', [
            'role' => $role,
            'meta' => self::roles()[$role],
            'clinicNotice' => $role === 'clinic',
        ]);
    }

    protected function registerView(string $role): View
    {
        return view('auth.register', [
            'role' => $role,
            'meta' => self::roles()[$role],
            'genders' => PatientRegistrationRequest::genders(),
        ]);
    }

    /**
     * Attempt to authenticate the request against the given guard.
     */
    protected function attempt(LoginRequest $request, string $guard): RedirectResponse
    {
        $credentials = $request->credentials();

        if (! Auth::guard($guard)->attempt($credentials)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        Auth::shouldUse($guard);
        $request->session()->regenerate();

        $user = Auth::guard($guard)->user();

        if ($guard === 'clinic' && ! $user->is_approved) {
            return $this->rejectClinicLogin($request, $user);
        }

        if ($guard === 'doctor') {
            $this->startDoctorSession($request, $user);
        }

        return redirect()
            ->intended(route($guard.'.dashboard'))
            ->with('success', 'Signed in as '.$user->display_name.'.');
    }

    /**
     * Clinics that are pending or rejected may never reach their dashboard.
     */
    protected function rejectClinicLogin(Request $request, Clinic $clinic): RedirectResponse
    {
        $message = $clinic->is_pending
            ? 'Your registration is pending administrative approval.'
            : 'Your clinic registration has been rejected by the administrator.';

        Auth::guard('clinic')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('clinic.login')
            ->with('approval_status', $clinic->status)
            ->with('approval_message', $message)
            ->with('error', $message);
    }

    /**
     * Record the doctor login in `doctor_session_logs` and remember the id.
     */
    protected function startDoctorSession(Request $request, Doctor $doctor): DoctorSessionLog
    {
        // Defensively close sessions left open by an earlier crash / hard reload.
        DoctorSessionLog::query()
            ->forDoctor($doctor->doctor_id)
            ->open()
            ->update(['logout_time' => now()]);

        $log = DoctorSessionLog::create([
            'doctor_id' => $doctor->doctor_id,
            'login_time' => now(),
        ]);

        $request->session()->put('doctor_log_id', $log->log_id);

        return $log;
    }

    /**
     * Stamp `logout_time` on the doctor session stored in the session.
     */
    protected function closeDoctorSession(Request $request): void
    {
        $logId = $request->session()->pull('doctor_log_id');

        if (! $logId) {
            return;
        }

        $log = DoctorSessionLog::find($logId);

        $log?->close();
    }

    protected function logoutGuard(Request $request, string $guard): RedirectResponse
    {
        Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route($guard.'.login')
            ->with('success', 'You have been signed out successfully.');
    }
}
