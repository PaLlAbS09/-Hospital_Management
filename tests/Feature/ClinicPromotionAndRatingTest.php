<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicAnnouncement;
use App\Models\ClinicOffer;
use App\Models\ClinicSchedule;
use App\Models\Doctor;
use App\Models\DoctorReview;
use App\Models\Patient;
use App\Notifications\DoctorRatingRequest;
use App\Services\DoctorRatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesHospitalSchema;
use Tests\TestCase;

class ClinicPromotionAndRatingTest extends TestCase
{
    use CreatesHospitalSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createHospitalSchema();
    }

    /* ---------------------------------------------------------------------
     | Clinic about section
     | ------------------------------------------------------------------- */

    public function test_clinic_can_publish_an_about_section(): void
    {
        $clinic = Clinic::factory()->approved()->create(['about' => null]);

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.about.update'), ['about' => 'A modern diagnostic centre in Bardhaman.'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clinics', [
            'clinic_id' => $clinic->clinic_id,
            'about' => 'A modern diagnostic centre in Bardhaman.',
        ]);
    }

    public function test_about_section_is_visible_on_the_public_clinic_profile(): void
    {
        $clinic = Clinic::factory()->approved()->create([
            'clinic_name' => 'Orthomed Diagnostic Center',
            'about' => 'Trusted orthopaedic care since 2004.',
        ]);

        $this->get(route('clinics.show', $clinic))
            ->assertOk()
            ->assertSee('Orthomed Diagnostic Center')
            ->assertSee('Trusted orthopaedic care since 2004.');
    }

    public function test_pending_clinic_profile_is_not_public(): void
    {
        $this->get(route('clinics.show', Clinic::factory()->pending()->create()))->assertNotFound();
    }

    /* ---------------------------------------------------------------------
     | Landing page slider (new doctors + offers)
     | ------------------------------------------------------------------- */

    public function test_clinic_can_publish_a_new_doctor_announcement(): void
    {
        $clinic = Clinic::factory()->approved()->create(['clinic_name' => 'Orthomed Diagnostic Center']);
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create(['specialization' => 'Orthopedics']);

        $this->actingAs($clinic, 'clinic')->post(route('clinic.announcements.store'), [
            'doctor_id' => $doctor->doctor_id,
            'department' => 'Orthopedics',
            'joining_date' => today()->addDays(5)->toDateString(),
            'joining_time' => '10:30',
            'message' => 'Consultation on the first day.',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('clinic_announcements', [
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'department' => 'Orthopedics',
            'is_active' => true,
        ]);
    }

    public function test_clinic_can_publish_a_discount_offer(): void
    {
        $clinic = Clinic::factory()->approved()->create();

        $this->actingAs($clinic, 'clinic')->post(route('clinic.offers.store'), [
            'title' => '15% off on every medicine',
            'description' => 'Discounted at the in-house pharmacy.',
            'discount_percent' => 15,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('clinic_offers', [
            'clinic_id' => $clinic->clinic_id,
            'discount_percent' => 15,
        ]);
    }

    public function test_offer_discount_is_validated(): void
    {
        $clinic = Clinic::factory()->approved()->create();

        $this->actingAs($clinic, 'clinic')->post(route('clinic.offers.store'), [
            'title' => 'Too much',
            'discount_percent' => 150,
        ])->assertSessionHasErrors('discount_percent');
    }

    public function test_landing_page_slider_shows_announcements_and_offers(): void
    {
        $clinic = Clinic::factory()->approved()->create(['clinic_name' => 'Orthomed Diagnostic Center']);
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create(['first_name' => 'M.', 'last_name' => 'Roy']);

        ClinicAnnouncement::create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'department' => 'Orthopedics',
            'joining_date' => today()->addDays(3)->toDateString(),
            'joining_time' => '11:00',
            'is_active' => true,
        ]);

        ClinicOffer::create([
            'clinic_id' => $clinic->clinic_id,
            'title' => '15% off on every medicine',
            'discount_percent' => 15,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('is joining');
        $response->assertSee('15% OFF');
        $response->assertSee('15% off on every medicine');
        $response->assertSee('hms-promo-slider', false);
    }

    public function test_inactive_and_pending_clinic_content_is_hidden_from_the_slider(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        $pending = Clinic::factory()->pending()->create();

        ClinicOffer::create([
            'clinic_id' => $clinic->clinic_id,
            'title' => 'Visible offer',
            'discount_percent' => 10,
            'is_active' => true,
        ]);

        ClinicOffer::create([
            'clinic_id' => $clinic->clinic_id,
            'title' => 'Hidden offer',
            'discount_percent' => 90,
            'is_active' => false,
        ]);

        ClinicOffer::create([
            'clinic_id' => $pending->clinic_id,
            'title' => 'Pending clinic offer',
            'discount_percent' => 55,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('Visible offer');
        $response->assertDontSee('Hidden offer');
        $response->assertDontSee('Pending clinic offer');
    }

    public function test_clinic_cannot_touch_another_clinics_announcement(): void
    {
        $mine = Clinic::factory()->approved()->create();
        $other = Clinic::factory()->approved()->create();

        $announcement = ClinicAnnouncement::create([
            'clinic_id' => $other->clinic_id,
            'department' => 'Cardiology',
            'joining_date' => today()->toDateString(),
            'joining_time' => '09:00',
            'is_active' => true,
        ]);

        $this->actingAs($mine, 'clinic')
            ->delete(route('clinic.announcements.destroy', $announcement))
            ->assertForbidden();

        $this->assertDatabaseHas('clinic_announcements', ['announcement_id' => $announcement->announcement_id]);
    }

    /* ---------------------------------------------------------------------
     | Rating request notification
     | ------------------------------------------------------------------- */

    public function test_completing_an_appointment_asks_the_patient_to_rate(): void
    {
        Notification::fake();

        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
            'status' => Appointment::STATUS_ACTIVE,
        ]);

        $this->actingAs($doctor, 'doctor')
            ->post(route('doctor.appointments.complete', $appointment))
            ->assertSessionHas('success');

        $this->assertSame(Appointment::STATUS_COMPLETED, $appointment->fresh()->status);
        Notification::assertSentTo($patient, DoctorRatingRequest::class);
    }

    public function test_marking_a_patient_as_arrived_asks_the_patient_to_rate(): void
    {
        Notification::fake();

        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
            'status' => Appointment::STATUS_ACTIVE,
            'checked_in_at' => null,
        ]);

        $this->actingAs($doctor, 'doctor')
            ->post(route('doctor.appointments.check-in', $appointment))
            ->assertSessionHas('success');

        Notification::assertSentTo($patient, DoctorRatingRequest::class);
    }

    public function test_rating_request_is_only_sent_once_per_appointment(): void
    {
        Notification::fake();

        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
            'status' => Appointment::STATUS_ACTIVE,
            'checked_in_at' => null,
        ]);

        $this->actingAs($doctor, 'doctor')->post(route('doctor.appointments.check-in', $appointment));
        $this->actingAs($doctor, 'doctor')->post(route('doctor.appointments.complete', $appointment));

        Notification::assertSentToTimes($patient, DoctorRatingRequest::class, 1);
    }

    /* ---------------------------------------------------------------------
     | Patient ratings
     | ------------------------------------------------------------------- */

    public function test_patient_can_rate_a_completed_appointment(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->completed()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        $this->actingAs($patient, 'patient')->get(route('patient.appointments.review.create', $appointment))
            ->assertOk()
            ->assertSee('Share your experience');

        $this->actingAs($patient, 'patient')->post(route('patient.appointments.review.store', $appointment), [
            'rating' => 5,
            'experience' => 'Very patient doctor and the clinic was spotless.',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('doctor_reviews', [
            'appointment_id' => $appointment->appointment_id,
            'doctor_id' => $doctor->doctor_id,
            'rating' => 5,
        ]);
    }

    public function test_rating_is_rejected_outside_the_one_to_five_range(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->completed()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        $this->actingAs($patient, 'patient')
            ->post(route('patient.appointments.review.store', $appointment), ['rating' => 9])
            ->assertSessionHasErrors('rating');

        $this->assertSame(0, DoctorReview::count());
    }

    public function test_cancelled_appointments_cannot_be_rated(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
            'status' => Appointment::STATUS_CANCELLED_BY_PATIENT,
        ]);

        $this->actingAs($patient, 'patient')
            ->get(route('patient.appointments.review.create', $appointment))
            ->assertNotFound();
    }

    public function test_patient_cannot_rate_someone_elses_appointment(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $owner */
        $owner = Patient::factory()->create();
        /** @var Patient $other */
        $other = Patient::factory()->create();
        $appointment = Appointment::factory()->completed()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $owner->patient_id,
        ]);

        $this->actingAs($other, 'patient')
            ->post(route('patient.appointments.review.store', $appointment), ['rating' => 1])
            ->assertForbidden();
    }

    public function test_an_appointment_can_only_be_rated_once(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->completed()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        $this->actingAs($patient, 'patient')
            ->post(route('patient.appointments.review.store', $appointment), ['rating' => 4]);

        $this->actingAs($patient, 'patient')
            ->post(route('patient.appointments.review.store', $appointment), ['rating' => 1])
            ->assertSessionHas('error');

        $this->assertSame(1, DoctorReview::count());
        $this->assertSame(4, DoctorReview::first()->rating);
    }

    /* ---------------------------------------------------------------------
     | Best doctor badges
     | ------------------------------------------------------------------- */

    public function test_badge_thresholds(): void
    {
        $service = app(DoctorRatingService::class);

        $this->assertNull($service->badgeFor(null, 0));
        // A single perfect review is not enough for the top badge.
        $this->assertSame(DoctorRatingService::BADGE_RECOMMENDED, $service->badgeFor(5.0, 1)['key']);
        $this->assertSame(DoctorRatingService::BADGE_BEST_CHOICE, $service->badgeFor(4.6, 4)['key']);
        // A high average without volume only earns the lower badge.
        $this->assertSame(DoctorRatingService::BADGE_RECOMMENDED, $service->badgeFor(4.4, 5)['key']);
        $this->assertNull($service->badgeFor(3.2, 8));
    }

    public function test_most_rated_doctor_wins_the_badge(): void
    {
        $clinic = Clinic::factory()->approved()->create();

        /** @var Doctor $roy */
        $roy = Doctor::factory()->create([
            'first_name' => 'M.',
            'last_name' => 'Roy',
            'specialization' => 'Orthopedics',
        ]);

        /** @var Doctor $akhilesh */
        $akhilesh = Doctor::factory()->create(['specialization' => 'Orthopedics']);

        // Both doctors must be published at this clinic to appear on its profile.
        ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $roy->doctor_id,
            'schedule_date' => today()->addDay()->toDateString(),
        ]);

        ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $akhilesh->doctor_id,
            'schedule_date' => today()->addDay()->toDateString(),
        ]);

        foreach (range(1, 3) as $index) {
            $appointment = Appointment::factory()->completed()->create([
                'clinic_id' => $clinic->clinic_id,
                'doctor_id' => $roy->doctor_id,
                'appointment_date' => today()->subDays($index)->toDateString(),
            ]);

            DoctorReview::create([
                'appointment_id' => $appointment->appointment_id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $roy->doctor_id,
                'clinic_id' => $clinic->clinic_id,
                'rating' => 5,
            ]);
        }

        $singleAppointment = Appointment::factory()->completed()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $akhilesh->doctor_id,
            'appointment_date' => today()->subDay()->toDateString(),
        ]);

        DoctorReview::create([
            'appointment_id' => $singleAppointment->appointment_id,
            'patient_id' => $singleAppointment->patient_id,
            'doctor_id' => $akhilesh->doctor_id,
            'clinic_id' => $clinic->clinic_id,
            'rating' => 4,
        ]);

        $leaderboard = app(DoctorRatingService::class)->leaderboard($clinic);

        $this->assertSame($roy->doctor_id, $leaderboard->first()['doctor']->doctor_id);
        $this->assertSame('Best choice in Orthopedics', $leaderboard->first()['badge_label']);

        $this->get(route('clinics.show', $clinic))
            ->assertOk()
            ->assertSee('Best choice in Orthopedics');
    }

    public function test_landing_page_shows_best_doctors(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create(['specialization' => 'Orthopedics']);

        $appointment = Appointment::factory()->completed()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
        ]);

        DoctorReview::create([
            'appointment_id' => $appointment->appointment_id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'clinic_id' => $clinic->clinic_id,
            'rating' => 5,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Chosen by patients')
            ->assertSee('Dr. '.$doctor->full_name)
            ->assertSee('Highly recommended in Orthopedics');
    }

    public function test_private_reviews_do_not_influence_the_leaderboard(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();

        $appointment = Appointment::factory()->completed()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
        ]);

        DoctorReview::create([
            'appointment_id' => $appointment->appointment_id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'clinic_id' => $clinic->clinic_id,
            'rating' => 1,
            'is_public' => false,
        ]);

        $this->assertTrue(app(DoctorRatingService::class)->leaderboard($clinic)->isEmpty());
    }

    /* ---------------------------------------------------------------------
     | Clinic management screens render
     | ------------------------------------------------------------------- */

    public function test_clinic_management_screens_render(): void
    {
        $clinic = Clinic::factory()->approved()->create([
            'clinic_name' => 'Orthomed Diagnostic Center',
            'about' => 'Trusted orthopaedic care.',
        ]);

        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create(['specialization' => 'Orthopedics']);

        ClinicAnnouncement::create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'department' => 'Orthopedics',
            'joining_date' => today()->addDays(3)->toDateString(),
            'joining_time' => '11:00',
            'is_active' => true,
        ]);

        ClinicOffer::create([
            'clinic_id' => $clinic->clinic_id,
            'title' => '15% off on every medicine',
            'discount_percent' => 15,
            'is_active' => true,
        ]);

        $this->actingAs($clinic, 'clinic')->get(route('clinic.about.edit'))
            ->assertOk()
            ->assertSee('About section')
            ->assertSee('Trusted orthopaedic care.');

        // The announcements page orders through the model's latestFirst() scope.
        $this->actingAs($clinic, 'clinic')->get(route('clinic.announcements.index'))
            ->assertOk()
            ->assertSee('Dr. '.$doctor->full_name)
            ->assertSee('Orthopedics');

        $this->actingAs($clinic, 'clinic')->get(route('clinic.offers.index'))
            ->assertOk()
            ->assertSee('15% off on every medicine')
            ->assertSee('15% OFF');
    }

    public function test_announcements_are_ordered_with_the_nearest_joining_date_first(): void
    {
        $clinic = Clinic::factory()->approved()->create();

        $later = ClinicAnnouncement::create([
            'clinic_id' => $clinic->clinic_id,
            'department' => 'Cardiology',
            'joining_date' => today()->addDays(10)->toDateString(),
            'joining_time' => '09:00',
            'is_active' => true,
        ]);

        $sooner = ClinicAnnouncement::create([
            'clinic_id' => $clinic->clinic_id,
            'department' => 'Neurology',
            'joining_date' => today()->addDay()->toDateString(),
            'joining_time' => '09:00',
            'is_active' => true,
        ]);

        $ordered = $clinic->announcements()->latestFirst()->get();

        $this->assertSame(
            [$sooner->announcement_id, $later->announcement_id],
            $ordered->pluck('announcement_id')->all()
        );
    }

    public function test_toggling_an_announcement_hides_and_restores_it(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        $announcement = ClinicAnnouncement::create([
            'clinic_id' => $clinic->clinic_id,
            'department' => 'Cardiology',
            'joining_date' => today()->toDateString(),
            'joining_time' => '09:00',
            'is_active' => true,
        ]);

        $this->actingAs($clinic, 'clinic')
            ->patch(route('clinic.announcements.toggle', $announcement))
            ->assertSessionHas('success');

        $this->assertFalse($announcement->fresh()->is_active);

        $this->actingAs($clinic, 'clinic')
            ->patch(route('clinic.announcements.toggle', $announcement));

        $this->assertTrue($announcement->fresh()->is_active);
    }

    public function test_find_a_clinic_cards_show_why_choose_summary(): void
    {
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $clinic = Clinic::factory()->approved()->create([
            'clinic_name' => 'OrthoMed Diagnostic Center',
            'area' => 'Burdwan',
            'about' => 'Advanced orthopaedic implants and sports injury care.',
        ]);

        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create(['specialization' => 'Orthopedic']);

        ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'schedule_date' => today()->addDay()->toDateString(),
        ]);

        ClinicOffer::create([
            'clinic_id' => $clinic->clinic_id,
            'title' => '15% off on every medicine',
            'discount_percent' => 15,
            'is_active' => true,
        ]);

        ClinicAnnouncement::create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'department' => 'Orthopedic',
            'joining_date' => today()->addDays(4)->toDateString(),
            'joining_time' => '10:00',
            'is_active' => true,
        ]);

        $appointment = Appointment::factory()->completed()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
        ]);

        DoctorReview::create([
            'appointment_id' => $appointment->appointment_id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'clinic_id' => $clinic->clinic_id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($patient, 'patient')
            ->get(route('patient.clinics.index', ['area' => 'Burdwan']))
            ->assertOk()
            ->assertSee('Advanced orthopaedic implants')
            ->assertSee('15% OFF')
            ->assertSee('New doctor joining')
            ->assertSee('1 doctor')
            ->assertSee('5.0');

        // One action per card, and it opens the clinic profile rather than the
        // booking screen.
        $response->assertSee(route('patient.clinics.show', $clinic), false);
        $response->assertDontSee(route('patient.clinics.doctors', $clinic), false);
        $response->assertDontSee('Why choose this clinic');
        $response->assertDontSee('See doctors &amp; slots');
    }

    /* ---------------------------------------------------------------------
     | Patient portal: "why choose this clinic" step
     | ------------------------------------------------------------------- */

    public function test_patient_dashboard_prompts_for_pending_reviews(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();
        /** @var Patient $patient */
        $patient = Patient::factory()->create();

        Appointment::factory()->completed()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $patient->patient_id,
        ]);

        $this->actingAs($patient, 'patient')
            ->get(route('patient.dashboard'))
            ->assertOk()
            ->assertSee('Rate your recent visit');
    }

    public function test_patient_can_open_a_clinic_profile_inside_the_portal(): void
    {
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $clinic = Clinic::factory()->approved()->create([
            'clinic_name' => 'OrthoMed Diagnostic Center',
            'area' => 'Burdwan',
            'about' => 'Advanced orthopaedic implants and sports injury care.',
        ]);

        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create([
            'first_name' => 'M.M.',
            'last_name' => 'Roy',
            'specialization' => 'Orthopedic',
            'experience_years' => 18,
            'experience_note' => 'Joint replacement and sports injury surgery.',
        ]);

        ClinicSchedule::factory()->create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'schedule_date' => today()->addDay()->toDateString(),
        ]);

        ClinicOffer::create([
            'clinic_id' => $clinic->clinic_id,
            'title' => '15% off on every medicine',
            'discount_percent' => 15,
            'is_active' => true,
        ]);

        ClinicAnnouncement::create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctor->doctor_id,
            'department' => 'Orthopedic',
            'joining_date' => today()->addDays(4)->toDateString(),
            'joining_time' => '10:00',
            'is_active' => true,
        ]);

        foreach (range(1, 3) as $index) {
            $appointment = Appointment::factory()->completed()->create([
                'clinic_id' => $clinic->clinic_id,
                'doctor_id' => $doctor->doctor_id,
                'appointment_date' => today()->subDays($index)->toDateString(),
            ]);

            DoctorReview::create([
                'appointment_id' => $appointment->appointment_id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'clinic_id' => $clinic->clinic_id,
                'rating' => 5,
                'experience' => 'Explained everything clearly.',
            ]);
        }

        $this->actingAs($patient, 'patient')
            ->get(route('patient.clinics.show', $clinic))
            ->assertOk()
            // About section
            ->assertSee('About this clinic')
            ->assertSee('Advanced orthopaedic implants and sports injury care.')
            // Offers
            ->assertSee('15% off on every medicine')
            // New doctor promotion
            ->assertSee('Doctors joining soon')
            // Doctor, experience and the public badge
            ->assertSee('Dr. M.M. Roy')
            ->assertSee('18 years experience')
            ->assertSee('Best choice in Orthopedic')
            // Patient reviews
            ->assertSee('Explained everything clearly.')
            // And the path onwards to booking
            ->assertSee(route('patient.clinics.doctors', $clinic), false);
    }

    public function test_patient_clinic_profile_requires_authentication(): void
    {
        $clinic = Clinic::factory()->approved()->create();

        // The framework's `login` route is aliased to the landing page.
        $this->get(route('patient.clinics.show', $clinic))
            ->assertRedirect(route('home'));
    }

    public function test_pending_clinic_is_hidden_from_the_patient_profile(): void
    {
        /** @var Patient $patient */
        $patient = Patient::factory()->create();

        $this->actingAs($patient, 'patient')
            ->get(route('patient.clinics.show', Clinic::factory()->pending()->create()))
            ->assertNotFound();
    }

    public function test_booking_page_links_back_to_the_clinic_profile(): void
    {
        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $clinic = Clinic::factory()->approved()->create();

        $this->actingAs($patient, 'patient')
            ->get(route('patient.clinics.doctors', $clinic))
            ->assertOk()
            ->assertSee('Back to clinic profile')
            ->assertSee(route('patient.clinics.show', $clinic), false);
    }
}
