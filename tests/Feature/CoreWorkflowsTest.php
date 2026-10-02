<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicSchedule;
use App\Models\Doctor;
use App\Models\Patient;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentConfirmed;
use App\Notifications\AppointmentNoShow;
use App\Notifications\ClinicApproved;
use App\Services\AppointmentBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesHospitalSchema;
use Tests\TestCase;

class CoreWorkflowsTest extends TestCase
{
    use CreatesHospitalSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createHospitalSchema();
    }

    public function test_patient_can_book_appointment(): void
    {
        Notification::fake();

        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $date = today()->addDay()->toDateString();

        $schedule = ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'schedule_date' => $date,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'patient_capacity' => 10,
        ]);

        $slot = $schedule->slotTimes()[0];

        $this->actingAs($patient, 'patient')->post(route('patient.appointments.store'), [
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'appointment_date' => $date,
            'appointment_time' => $slot,
        ])->assertRedirect(route('patient.dashboard'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->patient_id,
            'status' => Appointment::STATUS_ACTIVE,
        ]);

        Notification::assertSentTo($patient, AppointmentConfirmed::class);
    }

    public function test_booking_rejected_when_full(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $date = today()->addDay()->toDateString();

        ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'schedule_date' => $date,
            'start_time' => '09:00',
            'end_time' => '09:30',
            'patient_capacity' => 1,
        ]);

        /** @var Patient $other */
        $other = Patient::factory()->create();

        app(AppointmentBookingService::class)->book(
            $other, $clinic->clinic_id, $doctor->doctor_id, $date, '09:00'
        );

        $this->actingAs($patient, 'patient')->post(route('patient.appointments.store'), [
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'appointment_date' => $date,
            'appointment_time' => '09:00',
        ])->assertSessionHas('error');
    }

    public function test_booking_availability_endpoint_returns_slots_for_selected_date(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $dateOne = today()->addDay()->toDateString();
        $dateTwo = today()->addDays(2)->toDateString();

        ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'schedule_date' => $dateOne,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'patient_capacity' => 10,
        ]);

        ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'schedule_date' => $dateTwo,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'patient_capacity' => 10,
        ]);

        $response = $this->actingAs($patient, 'patient')->getJson(
            route('patient.clinics.availability', $clinic).'?date='.$dateTwo
        );

        $response->assertOk()
            ->assertJsonPath('date', $dateTwo)
            ->assertJsonStructure(['date', 'date_label', 'available_dates', 'offers_html', 'booking_notice_html']);

        $this->assertStringContainsString('14:00', $response->json('offers_html'));
        $this->assertStringNotContainsString('09:00', $response->json('offers_html'));
    }

    public function test_booking_page_shows_active_date_chip(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $date = today()->addDay()->toDateString();

        ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'schedule_date' => $date,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'patient_capacity' => 10,
        ]);

        $response = $this->actingAs($patient, 'patient')
            ->get(route('patient.clinics.doctors', ['clinic' => $clinic, 'date' => $date]));

        $response->assertOk();
        $response->assertSee('id="active-date-chip"', false);
        $response->assertSee('data-active-date="'.$date.'"', false);
        $response->assertSee('id="booking-offers-wrap"', false);
        $response->assertSee('id="booking-date-chips"', false);
    }

    public function test_booking_stores_contact_phone_and_sends_confirmation_email(): void
    {
        Notification::fake();

        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $date = today()->addDay()->toDateString();

        $schedule = ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'schedule_date' => $date,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'patient_capacity' => 10,
        ]);

        $slot = $schedule->slotTimes()[0];

        $this->actingAs($patient, 'patient')->post(route('patient.appointments.store'), [
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'appointment_date' => $date,
            'appointment_time' => $slot,
            'contact_phone' => '9876543210',
        ])->assertRedirect(route('patient.dashboard'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->patient_id,
            'contact_phone' => '9876543210',
            'status' => Appointment::STATUS_ACTIVE,
        ]);

        Notification::assertSentTo($patient, AppointmentConfirmed::class);
    }

    public function test_check_in_marks_arrival_and_protects_no_show_sweep(): void
    {
        Notification::fake();

        /** @var Admin $admin */
        $admin = Admin::create([
            'name' => 'Reception',
            'email' => 'reception@example.com',
            'password' => Hash::make('password123'),
        ]);
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'appointment_date' => today()->toDateString(),
            'appointment_time' => now()->subHour()->format('H:i'),
            'status' => Appointment::STATUS_ACTIVE,
            'checked_in_at' => null,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.appointments.check-in', $appointment))
            ->assertSessionHas('success');

        $this->assertNotNull($appointment->fresh()->checked_in_at);

        $cancelled = app(AppointmentBookingService::class)->cancelExpiredNoShows();

        $this->assertSame(0, $cancelled);
        $this->assertSame(Appointment::STATUS_ACTIVE, $appointment->fresh()->status);
    }

    public function test_no_show_sweep_auto_cancels_and_emails_patient(): void
    {
        Notification::fake();

        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'appointment_date' => today()->toDateString(),
            'appointment_time' => now()->subHour()->format('H:i'),
            'status' => Appointment::STATUS_ACTIVE,
            'checked_in_at' => null,
        ]);

        $cancelled = app(AppointmentBookingService::class)->cancelExpiredNoShows();

        $this->assertSame(1, $cancelled);
        $this->assertSame(Appointment::STATUS_NO_SHOW, $appointment->fresh()->status);
        Notification::assertSentTo($patient, AppointmentNoShow::class);
    }

    public function test_manual_cancellation_emails_patient(): void
    {
        Notification::fake();

        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'status' => Appointment::STATUS_ACTIVE,
        ]);

        $this->actingAs($patient, 'patient')
            ->post(route('patient.appointments.cancel', $appointment))
            ->assertSessionHas('success');

        $this->assertSame(Appointment::STATUS_CANCELLED_BY_PATIENT, $appointment->fresh()->status);
        Notification::assertSentTo($patient, AppointmentCancelled::class);
    }

    public function test_clinic_approval_blocks_pending_and_rejected(): void
    {
        $pending = Clinic::factory()->pending()->create();
        $rejected = Clinic::factory()->rejected()->create();
        $approved = Clinic::factory()->approved()->create();

        $this->actingAs($pending, 'clinic')
            ->get(route('clinic.dashboard'))
            ->assertRedirect(route('clinic.login'));

        $this->assertGuest('clinic');

        $this->actingAs($rejected, 'clinic')
            ->get(route('clinic.dashboard'))
            ->assertRedirect(route('clinic.login'));

        $this->assertGuest('clinic');

        $this->actingAs($approved, 'clinic')
            ->get(route('clinic.dashboard'))
            ->assertOk();
    }

    public function test_admin_approval_sends_notification(): void
    {
        Notification::fake();

        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
        $clinic = Clinic::factory()->pending()->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.clinics.approve', $clinic))
            ->assertRedirect();

        $this->assertSame(Clinic::STATUS_APPROVED, $clinic->fresh()->status);
        Notification::assertSentTo($clinic->fresh(), ClinicApproved::class);
    }

    public function test_doctor_can_update_prescription(): void
    {
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->doctor_id,
        ]);

        $this->actingAs($doctor, 'doctor')->post(
            route('doctor.appointments.prescription', $appointment),
            [
                'disease' => 'Viral fever',
                'allergies' => 'None',
                'prescription_details' => 'Paracetamol 500mg twice daily.',
            ]
        )->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'appointment_id' => $appointment->appointment_id,
            'disease' => 'Viral fever',
        ]);
    }

    public function test_doctor_cannot_update_other_prescription(): void
    {
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Doctor $other */
        $other = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id' => $other->doctor_id,
            'disease' => null,
            'allergies' => null,
            'prescription_details' => null,
        ]);

        $this->actingAs($doctor, 'doctor')->post(
            route('doctor.appointments.prescription', $appointment->appointment_id),
            [
                'disease' => 'Migraine',
                'allergies' => 'None',
                'prescription_details' => 'Rest and hydration.',
            ]
        )->assertForbidden();

        $this->assertDatabaseHas('appointments', [
            'appointment_id' => $appointment->appointment_id,
            'disease' => null,
            'prescription_details' => null,
        ]);
    }

    public function test_prescription_request_rejects_oversized(): void
    {
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->doctor_id,
        ]);

        $this->actingAs($doctor, 'doctor')->post(
            route('doctor.appointments.prescription', $appointment),
            ['prescription_details' => str_repeat('a', 5001)]
        )->assertSessionHasErrors('prescription_details');
    }
}
