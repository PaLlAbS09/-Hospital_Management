<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/clinics', [HomeController::class, 'clinics'])->name('clinics.index');

Route::post('/contact', [HomeController::class, 'storeQuery'])
    ->middleware('throttle:10,1')
    ->name('contact.store');

// Safety net for the framework redirect helpers (they look for a `login` route).
Route::get('/login', fn () => redirect()->route('home'))->name('login');
Route::get('/register', fn () => redirect()->route('patient.register'))->name('register');

/*
|--------------------------------------------------------------------------
| Guest routes (login / registration for every role)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Administrator
    Route::get('admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.store');

    // Clinic
    Route::get('clinic/login', [AuthController::class, 'showClinicLoginForm'])->name('clinic.login');
    Route::post('clinic/login', [AuthController::class, 'clinicLogin'])->name('clinic.login.store');
    Route::get('clinic/register', [AuthController::class, 'showClinicRegisterForm'])->name('clinic.register');
    Route::post('clinic/register', [AuthController::class, 'registerClinic'])->name('clinic.register.store');

    // Doctor
    Route::get('doctor/login', [AuthController::class, 'showDoctorLoginForm'])->name('doctor.login');
    Route::post('doctor/login', [AuthController::class, 'doctorLogin'])->name('doctor.login.store');

    // Patient
    Route::get('patient/login', [AuthController::class, 'showPatientLoginForm'])->name('patient.login');
    Route::post('patient/login', [AuthController::class, 'patientLogin'])->name('patient.login.store');
    Route::get('patient/register', [AuthController::class, 'showPatientRegisterForm'])->name('patient.register');
    Route::post('patient/register', [AuthController::class, 'registerPatient'])->name('patient.register.store');
});

/*
|--------------------------------------------------------------------------
| Administrator area - /admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('logout', [AuthController::class, 'adminLogout'])->name('logout');

        // Clinic management
        Route::get('clinics', [AdminController::class, 'clinics'])->name('clinics.index');
        Route::post('clinics/{clinic}/approve', [AdminController::class, 'approveClinic'])->name('clinics.approve');
        Route::post('clinics/{clinic}/reject', [AdminController::class, 'rejectClinic'])->name('clinics.reject');

        // Doctor management
        Route::get('doctors/create', [AdminController::class, 'createDoctor'])->name('doctors.create');
        Route::get('doctors', [AdminController::class, 'doctors'])->name('doctors.index');
        Route::post('doctors', [AdminController::class, 'storeDoctor'])->name('doctors.store');
        Route::delete('doctors/{doctor}', [AdminController::class, 'destroyDoctor'])->name('doctors.destroy');
        // Compatibility alias for the legacy "/admin/new/doctors" URL.
        Route::get('new/doctors', fn () => redirect()->route('admin.doctors.create'))->name('doctors.new');

        // Patient directory
        Route::get('patients', [AdminController::class, 'patients'])->name('patients.index');
        Route::get('patients/{patient}', [AdminController::class, 'showPatient'])->name('patients.show');

        // Appointments & prescriptions
        Route::get('appointments', [AdminController::class, 'appointments'])->name('appointments.index');

        // Doctor session logs
        Route::get('session-logs', [AdminController::class, 'sessionLogs'])->name('session-logs.index');

        // Contact queries
        Route::get('queries', [AdminController::class, 'queries'])->name('queries.index');
        Route::delete('queries/{query}', [AdminController::class, 'destroyQuery'])->name('queries.destroy');
    });

/*
|--------------------------------------------------------------------------
| Clinic area - /clinic  (approved clinics only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:clinic', 'clinic.approval', 'role:clinic'])
    ->prefix('clinic')
    ->name('clinic.')
    ->group(function () {
        Route::get('/', [ClinicController::class, 'dashboard'])->name('dashboard');
        Route::post('logout', [AuthController::class, 'clinicLogout'])->name('logout');

        // Schedule management
        Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

        // Localised appointments
        Route::get('appointments', [ClinicController::class, 'appointments'])->name('appointments.index');
    });

/*
|--------------------------------------------------------------------------
| Doctor area - /doctor
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:doctor', 'role:doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        Route::get('/', [DoctorController::class, 'dashboard'])->name('dashboard');
        Route::post('logout', [AuthController::class, 'doctorLogout'])->name('logout');

        Route::get('queue', [DoctorController::class, 'queue'])->name('queue');
        Route::post('appointments/{appointment}/complete', [DoctorController::class, 'complete'])
            ->name('appointments.complete');
        Route::post('appointments/{appointment}/cancel', [DoctorController::class, 'cancel'])
            ->name('appointments.cancel');
        Route::post('appointments/{appointment}/prescription', [DoctorController::class, 'savePrescription'])
            ->name('appointments.prescription');
    });

/*
|--------------------------------------------------------------------------
| Patient area - /patient
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:patient', 'role:patient'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function () {
        Route::get('/', [PatientController::class, 'dashboard'])->name('dashboard');
        Route::post('logout', [AuthController::class, 'patientLogout'])->name('logout');

        // Search & booking engine
        Route::get('clinics', [PatientController::class, 'clinics'])->name('clinics.index');
        Route::get('clinics/{clinic}/doctors', [PatientController::class, 'clinicDoctors'])->name('clinics.doctors');
        Route::post('appointments', [PatientController::class, 'book'])->name('appointments.store');
        Route::post('appointments/{appointment}/cancel', [PatientController::class, 'cancel'])
            ->name('appointments.cancel');
        Route::get('appointments/{appointment}/prescription', [PatientController::class, 'prescription'])
            ->name('appointments.prescription');
    });

