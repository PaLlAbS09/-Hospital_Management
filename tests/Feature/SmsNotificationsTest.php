<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicSchedule;
use App\Models\Doctor;
use App\Models\Patient;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentConfirmed;
use App\Notifications\AppointmentNoShow;
use App\Notifications\Channels\SmsChannel;
use App\Services\AppointmentBookingService;
use App\Support\Sms\SmsGateway;
use App\Support\Sms\SmsMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\Concerns\CreatesHospitalSchema;
use Tests\TestCase;

class SmsNotificationsTest extends TestCase
{
    use CreatesHospitalSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createHospitalSchema();
    }

    /**
     * Replace the SMS gateway with a recorder and hand it back for assertions.
     */
    private function recordSms(): object
    {
        $gateway = new class implements SmsGateway
        {
            /** @var array<int, SmsMessage> */
            public array $messages = [];

            public function send(SmsMessage $message): void
            {
                $this->messages[] = $message;
            }
        };

        $this->app->instance(SmsGateway::class, $gateway);

        return $gateway;
    }

    /**
     * Create a bookable schedule for tomorrow and return [doctor, schedule, date].
     *
     * @return array{0: Doctor, 1: ClinicSchedule, 2: string}
     */
    private function bookableSlot(): array
    {
        $clinic = Clinic::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $date = today()->addDay()->toDateString();

        $schedule = ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'schedule_date' => $date,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'patient_capacity' => 10,
        ]);

        return [$doctor, $schedule, $date];
    }

    public function test_booking_sends_sms_to_the_phone_entered_on_the_booking_form(): void
    {
        $gateway = $this->recordSms();
        [$doctor, $schedule, $date] = $this->bookableSlot();

        /** @var Patient $patient */
        $patient = Patient::factory()->create(['contact' => '9000000000']);

        $this->actingAs($patient, 'patient')->post(route('patient.appointments.store'), [
            'clinic_id' => $schedule->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'appointment_date' => $date,
            'appointment_time' => $schedule->slotTimes()[0],
            'contact_phone' => '9876543210',
        ])->assertSessionHas('success');

        $this->assertCount(1, $gateway->messages);
        $this->assertSame('9876543210', $gateway->messages[0]->to);
        $this->assertSame('+919876543210', $gateway->messages[0]->toNumber());
        $this->assertStringContainsString('confirmed', $gateway->messages[0]->content);
    }

    public function test_booking_sms_falls_back_to_the_patient_profile_number(): void
    {
        $gateway = $this->recordSms();
        [$doctor, $schedule, $date] = $this->bookableSlot();

        /** @var Patient $patient */
        $patient = Patient::factory()->create(['contact' => '9000000000']);

        $this->actingAs($patient, 'patient')->post(route('patient.appointments.store'), [
            'clinic_id' => $schedule->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'appointment_date' => $date,
            'appointment_time' => $schedule->slotTimes()[0],
        ])->assertSessionHas('success');

        $this->assertCount(1, $gateway->messages);
        $this->assertSame('9000000000', $gateway->messages[0]->to);
    }

    public function test_no_show_auto_cancellation_sends_sms_to_the_patient(): void
    {
        $gateway = $this->recordSms();

        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'contact_phone' => '8123456789',
            'appointment_date' => today()->toDateString(),
            'appointment_time' => now()->subHour()->format('H:i'),
            'status' => Appointment::STATUS_ACTIVE,
            'checked_in_at' => null,
        ]);

        $cancelled = app(AppointmentBookingService::class)->cancelExpiredNoShows();

        $this->assertSame(1, $cancelled);
        $this->assertSame(Appointment::STATUS_NO_SHOW, $appointment->fresh()->status);
        $this->assertCount(1, $gateway->messages);
        $this->assertSame('8123456789', $gateway->messages[0]->to);
        $this->assertStringContainsString('auto-cancelled', $gateway->messages[0]->content);
    }

    public function test_patient_cancellation_sends_sms(): void
    {
        $gateway = $this->recordSms();

        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'contact_phone' => '8123456789',
            'status' => Appointment::STATUS_ACTIVE,
        ]);

        $this->actingAs($patient, 'patient')
            ->post(route('patient.appointments.cancel', $appointment))
            ->assertSessionHas('success');

        $this->assertCount(1, $gateway->messages);
        $this->assertStringContainsString('cancelled by you', $gateway->messages[0]->content);
    }

    public function test_channel_skips_sending_when_there_is_no_recipient_number(): void
    {
        $gateway = $this->recordSms();

        $notification = new class
        {
            public function toSms(object $notifiable): SmsMessage
            {
                return new SmsMessage('', 'No number on file.');
            }
        };

        (new SmsChannel($gateway))->send(Patient::factory()->create(), $notification);

        $this->assertCount(0, $gateway->messages);
    }

    public function test_appointment_without_a_booking_phone_uses_the_profile_number(): void
    {
        $gateway = $this->recordSms();

        /** @var Patient $patient */
        $patient = Patient::factory()->create(['contact' => '9000000000']);
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'contact_phone' => null,
            'status' => Appointment::STATUS_ACTIVE,
        ])->load(['patient', 'doctor', 'clinic']);

        (new SmsChannel($gateway))->send($patient, new AppointmentConfirmed($appointment));

        $this->assertCount(1, $gateway->messages);
        $this->assertSame('9000000000', $gateway->messages[0]->to);
    }

    public function test_failing_sms_gateway_does_not_break_the_booking_flow(): void
    {
        $this->app->instance(SmsGateway::class, new class implements SmsGateway
        {
            public function send(SmsMessage $message): void
            {
                throw new RuntimeException('SMS provider unreachable.');
            }
        });

        Log::spy();

        [$doctor, $schedule, $date] = $this->bookableSlot();

        /** @var Patient $patient */
        $patient = Patient::factory()->create(['contact' => '9000000000']);

        $response = $this->actingAs($patient, 'patient')->post(route('patient.appointments.store'), [
            'clinic_id' => $schedule->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'appointment_date' => $date,
            'appointment_time' => $schedule->slotTimes()[0],
            'contact_phone' => '9876543210',
        ]);

        // The booking still succeeds and still reports the email as delivered.
        $response->assertRedirect(route('patient.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->patient_id,
            'contact_phone' => '9876543210',
            'status' => Appointment::STATUS_ACTIVE,
        ]);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn (string $message) => str_starts_with($message, 'SMS delivery failed'));
    }

    public function test_appointment_notifications_expose_the_sms_channel(): void
    {
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'status' => Appointment::STATUS_ACTIVE,
        ])->load(['patient', 'doctor', 'clinic']);

        foreach ([
            new AppointmentConfirmed($appointment),
            new AppointmentCancelled($appointment),
            new AppointmentNoShow($appointment),
        ] as $notification) {
            $this->assertContains(SmsChannel::class, $notification->via($patient));
            $this->assertNotNull($notification->toSms($patient));
        }
    }

    public function test_sms_channel_is_listed_before_mail(): void
    {
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'status' => Appointment::STATUS_ACTIVE,
        ])->load(['patient', 'doctor', 'clinic']);

        foreach ([
            new AppointmentConfirmed($appointment),
            new AppointmentCancelled($appointment),
            new AppointmentNoShow($appointment),
        ] as $notification) {
            $channels = $notification->via($patient);
            $smsPosition = array_search(SmsChannel::class, $channels, true);
            $mailPosition = array_search('mail', $channels, true);

            $this->assertIsInt($smsPosition);
            $this->assertIsInt($mailPosition);
            $this->assertLessThan($mailPosition, $smsPosition);
        }
    }
}
