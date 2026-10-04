<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patient\StoreAppointmentRequest;
use App\Http\Requests\Patient\StoreReviewRequest;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicSchedule;
use App\Models\DoctorReview;
use App\Models\Patient;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentConfirmed;
use App\Services\AppointmentBookingException;
use App\Services\AppointmentBookingService;
use App\Services\ClinicProfileService;
use App\Support\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Patient area: /patient
 */
class PatientController extends Controller
{
    /**
     * Appointment history plus a quick summary.
     */
    public function dashboard(): View
    {
        $patient = $this->patient();
        $today = today()->toDateString();

        $appointments = $patient->appointments()
            ->with(['doctor', 'clinic', 'patient'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        $upcoming = $appointments
            ->filter(fn (Appointment $appointment) => $appointment->is_active
                && $appointment->appointment_date->toDateString() >= $today)
            ->sortBy(fn (Appointment $appointment) => $appointment->appointment_date->toDateString().' '.$appointment->appointment_time)
            ->values();

        $stats = [
            'total' => $appointments->count(),
            'upcoming' => $upcoming->count(),
            'completed' => $appointments->where('status', Appointment::STATUS_COMPLETED)->count(),
            'cancelled' => $appointments->whereIn('status', Appointment::cancelledStatuses())->count(),
            'prescriptions' => $appointments->filter(fn (Appointment $appointment) => $appointment->has_prescription)->count(),
            'pendingReviews' => $patient->pendingReviewAppointments()->count(),
        ];

        return view('patient.dashboard', [
            'patient' => $patient,
            'appointments' => $appointments,
            'upcoming' => $upcoming,
            'pendingReviews' => $patient->pendingReviewAppointments(),
            'stats' => $stats,
            'today' => $today,
        ]);
    }

    /* ---------------------------------------------------------------------
     | Search & booking engine
     | ------------------------------------------------------------------- */

    /**
     * Step 1 - filter approved clinics by geographic area.
     */
    public function clinics(Request $request, ClinicProfileService $profile): View
    {
        $area = $request->string('area')->trim()->toString();

        $areas = Clinic::query()
            ->approved()
            ->whereNotNull('area')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        $clinics = Clinic::query()
            ->approved()
            ->inArea($area)
            ->withCount('schedules')
            ->orderBy('area')
            ->orderBy('clinic_name')
            ->paginate(9)
            ->withQueryString();

        return view('patient.clinics', [
            'area' => $area,
            'areas' => $areas,
            'clinics' => $clinics,
            // About snippet, live offers, new-doctor promos and public rating
            // per clinic, so a patient can compare before opening one.
            'summaries' => $profile->summarise($clinics->getCollection()),
        ]);
    }

    /**
     * Step 2 - "why choose this clinic": about section, offers, new doctors,
     * doctor ratings and patient reviews, before moving on to slot booking.
     */
    public function clinicProfile(Clinic $clinic, ClinicProfileService $profile): View
    {
        abort_unless($clinic->is_approved, 404, 'This clinic is not available for booking.');

        return view('patient.clinic_profile', $profile->forClinic($clinic));
    }

    /**
     * Step 2 - pick a doctor and one of the published time slots.
     */
    public function clinicDoctors(Clinic $clinic, Request $request): View
    {
        abort_unless($clinic->is_approved, 404, 'This clinic is not available for booking.');

        $date = $this->normaliseDate($request->string('date')->trim()->toString());
        $data = $this->availabilityData($clinic, $date);

        return view('patient.clinic_doctors', array_merge(['clinic' => $clinic], $data));
    }

    /**
     * JSON availability for the booking date picker (no full reload needed).
     */
    public function clinicAvailability(Clinic $clinic, Request $request): JsonResponse
    {
        abort_unless($clinic->is_approved, 404, 'This clinic is not available for booking.');

        $date = $this->normaliseDate($request->string('date')->trim()->toString());
        $data = $this->availabilityData($clinic, $date);

        return response()->json([
            'clinic_id' => $clinic->clinic_id,
            'date' => $data['date'],
            'date_label' => Carbon::parse($data['date'])->format('D, d M Y'),
            'available_dates' => $data['availableDates'],
            'offers_html' => view('patient._offers_list', [
                'clinic' => $clinic,
                'date' => $data['date'],
                'offers' => $data['offers'],
            ])->render(),
            'booking_notice_html' => view('patient._booking_notice', [
                'myAppointments' => $data['myAppointments'],
            ])->render(),
        ]);
    }

    /**
     * Step 3 - book the appointment (status defaults to `Active`).
     */
    public function book(StoreAppointmentRequest $request, AppointmentBookingService $bookings): RedirectResponse
    {
        $patient = $this->patient();

        $contactPhone = $request->string('contact_phone')->trim()->toString() ?: null;

        try {
            $result = $bookings->book(
                $patient,
                $request->integer('clinic_id'),
                $request->integer('doctor_id'),
                $request->string('appointment_date')->toString(),
                $request->string('appointment_time')->toString(),
                $contactPhone
            );
        } catch (AppointmentBookingException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        /** @var Appointment $appointment */
        $appointment = $result['appointment'];
        $clinic = $result['schedule']->clinic ?? Clinic::query()->find($appointment->clinic_id);

        $appointment->load(['patient', 'doctor', 'clinic']);
        $emailSent = Notifier::send($patient, new AppointmentConfirmed($appointment));

        return redirect()
            ->route('patient.dashboard')
            ->with('success', 'Appointment booked with Dr. '.$appointment->doctor->full_name.' at '.($clinic?->clinic_name ?? 'the clinic').' on '.$appointment->appointment_date->format('d M Y').' at '.$appointment->formatted_time.'.'
                .($emailSent
                    ? ' A confirmation email was sent to '.$patient->email
                        .($appointment->notification_phone ? ' and an SMS to '.$appointment->notification_phone.'.' : '.')
                    : ' We could not send the confirmation email — please check the mail configuration.'));
    }

    /**
     * Cancel an active appointment (`Cancelled_by_Patient`).
     */
    public function cancel(Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->patient_id === $this->patient()->patient_id, 403);

        if ($appointment->status !== Appointment::STATUS_ACTIVE) {
            return back()->with('error', 'Only active appointments can be cancelled.');
        }

        $appointment->update(['status' => Appointment::STATUS_CANCELLED_BY_PATIENT]);
        $appointment->load(['patient', 'doctor', 'clinic']);
        $emailSent = $appointment->patient
            ? Notifier::send($appointment->patient, new AppointmentCancelled($appointment, 'patient'))
            : false;

        return back()->with('success', 'Your appointment has been cancelled.'
            .($emailSent
                ? ' A cancellation email was sent to '.($appointment->patient?->email ?? 'your email').'.'
                : ' We could not send the cancellation email.'));
    }

    /**
     * Rate a completed session and share the experience with other patients.
     */
    public function review(Appointment $appointment): View
    {
        $this->authorizeAppointment($appointment);

        abort_unless($appointment->is_reviewable, 404, 'Only completed appointments can be rated.');

        $appointment->load(['doctor', 'clinic', 'review']);

        abort_if($appointment->review !== null, 404, 'You have already reviewed this appointment.');

        return view('patient.review', [
            'appointment' => $appointment,
            'ratingScale' => DoctorReview::ratingScale(),
        ]);
    }

    /**
     * Store the rating. One review per appointment, enforced by a unique index.
     */
    public function storeReview(StoreReviewRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($appointment);

        if (! $appointment->is_reviewable) {
            return back()->with('error', 'Only completed appointments can be rated.');
        }

        if ($appointment->review()->exists()) {
            return back()->with('error', 'You have already reviewed this appointment.');
        }

        $patient = $this->patient();

        DoctorReview::create([
            'appointment_id' => $appointment->appointment_id,
            'patient_id' => $patient->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'clinic_id' => $appointment->clinic_id,
            'rating' => $request->integer('rating'),
            'experience' => $request->input('experience'),
            'is_public' => $request->boolean('is_public', true),
        ]);

        $appointment->load('doctor');

        return back()->with('success', 'Thank you! Your rating for Dr. '.$appointment->doctor->full_name.' has been published.');
    }

    /**
     * Printable prescription.
     */
    public function prescription(Appointment $appointment): View
    {
        abort_unless($appointment->patient_id === $this->patient()->patient_id, 403);

        $appointment->load(['patient', 'doctor', 'clinic']);

        return view('patient.prescription', compact('appointment'));
    }

    protected function patient(): Patient
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();

        abort_unless($patient instanceof Patient, 403);

        return $patient;
    }

    /** Patients may only reach their own appointments. */
    protected function authorizeAppointment(Appointment $appointment): void
    {
        abort_unless($appointment->patient_id === $this->patient()->patient_id, 403);
    }

    protected function normaliseDate(?string $date): string
    {
        $today = today()->toDateString();

        if (! filled($date)) {
            return $today;
        }

        try {
            $parsed = Carbon::parse($date)->toDateString();
        } catch (\Throwable $exception) {
            return $today;
        }

        return $parsed < $today ? $today : $parsed;
    }

    /**
     * Shared availability query for the booking page + JSON endpoint.
     *
     * @return array{date: string, offers: Collection<int, array{schedule: ClinicSchedule, slots: array}>, availableDates: Collection<int, string>, myAppointments: \Illuminate\Database\Eloquent\Collection<int, Appointment>}
     */
    protected function availabilityData(Clinic $clinic, string $date): array
    {
        $schedules = $clinic->schedules()
            ->with('doctor')
            ->onDate($date)
            ->ordered()
            ->get();

        $offers = $schedules->map(fn ($schedule) => [
            'schedule' => $schedule,
            'slots' => $schedule->slotAvailability(),
        ]);

        $availableDates = $clinic->schedules()
            ->onOrAfter(today()->toDateString())
            ->orderBy('schedule_date')
            ->pluck('schedule_date')
            ->map(fn ($value) => Carbon::parse($value)->toDateString())
            ->unique()
            ->take(30)
            ->values();

        $myAppointments = $this->patient()
            ->appointments()
            ->with('doctor')
            ->onDate($date)
            ->get();

        return [
            'date' => $date,
            'offers' => $offers,
            'availableDates' => $availableDates,
            'myAppointments' => $myAppointments,
        ];
    }
}
