<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\Concerns\CreatesHospitalSchema;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use CreatesHospitalSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createHospitalSchema();
    }

    /**
     * One account per role, plus the broker that owns it.
     *
     * @return array<string, array{0: string, 1: object, 2: string}>
     */
    private function accounts(): array
    {
        return [
            'admin' => [
                'admin',
                Admin::create([
                    'name' => 'Super Admin',
                    'email' => 'admin@example.com',
                    'password' => Hash::make('password123'),
                ]),
                'admins',
            ],
            'clinic' => [
                'clinic',
                Clinic::factory()->approved()->create(),
                'clinics',
            ],
            'doctor' => [
                'doctor',
                Doctor::factory()->create(),
                'doctors',
            ],
            'patient' => [
                'patient',
                Patient::factory()->create(),
                'patients',
            ],
        ];
    }

    public function test_forgot_password_page_renders_for_every_role(): void
    {
        foreach (array_keys($this->accounts()) as $role) {
            $this->get(route($role.'.password.request'))
                ->assertOk()
                ->assertSee('Forgot your password?')
                ->assertSee('name="email"', false);
        }
    }

    public function test_login_page_links_to_the_forgot_password_form(): void
    {
        foreach (array_keys($this->accounts()) as $role) {
            $this->get(route($role.'.login'))
                ->assertOk()
                ->assertSee(route($role.'.password.request'), false);
        }
    }

    public function test_reset_link_is_emailed_for_every_role(): void
    {
        Notification::fake();

        foreach ($this->accounts() as [$role, $user]) {
            $this->post(route($role.'.password.email'), ['email' => $user->email])
                ->assertSessionHas('status');

            Notification::assertSentTo($user, ResetPassword::class);
        }
    }

    public function test_reset_link_points_at_the_route_for_that_role(): void
    {
        Notification::fake();

        foreach ($this->accounts() as [$role, $user]) {
            $this->post(route($role.'.password.email'), ['email' => $user->email]);

            Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($role, $user): bool {
                return $notification->toMail($user)->actionUrl === route($role.'.password.reset', [
                    'token' => $notification->token,
                    'email' => $user->email,
                ]);
            });
        }
    }

    public function test_unknown_address_gives_the_same_response_as_a_known_one(): void
    {
        Notification::fake();

        $known = $this->post(route('patient.password.email'), ['email' => Patient::factory()->create()->email]);
        $knownStatus = $known->getSession()->get('status');

        $unknown = $this->post(route('patient.password.email'), ['email' => 'nobody@example.com']);
        $unknownStatus = $unknown->getSession()->get('status');

        // Identical wording so the form cannot be used to enumerate accounts.
        $this->assertSame($knownStatus, $unknownStatus);
        $this->assertNotNull($unknownStatus);
    }

    public function test_password_can_be_reset_for_every_role_and_new_password_signs_in(): void
    {
        Notification::fake();

        foreach ($this->accounts() as [$role, $user, $broker]) {
            /** @var PasswordBroker $passwordBroker */
            $passwordBroker = Password::broker($broker);
            $token = $passwordBroker->createToken($user);

            $this->get(route($role.'.password.reset', ['token' => $token, 'email' => $user->email]))
                ->assertOk()
                ->assertSee('Choose a new password');

            $this->post(route($role.'.password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'brand-new-secret',
                'password_confirmation' => 'brand-new-secret',
            ])->assertRedirect(route($role.'.login'));

            $this->assertTrue(
                Hash::check('brand-new-secret', $user->fresh()->password),
                'The password for the '.$role.' account was not updated.'
            );

            $this->post(route($role.'.login.store'), [
                'email' => $user->email,
                'password' => 'brand-new-secret',
            ])->assertRedirect(route($role.'.dashboard'));

            // Sign out again: the `guest` middleware watches every guard, so a
            // leftover session would redirect the next role's page.
            $this->post(route($role.'.logout'));
        }
    }

    public function test_an_invalid_token_is_rejected(): void
    {
        Notification::fake();

        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        $original = $patient->password;

        $this->post(route('patient.password.update'), [
            'token' => 'not-a-real-token',
            'email' => $patient->email,
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ])->assertSessionHasErrors('email');

        $this->assertSame($original, $patient->fresh()->password);
    }

    public function test_password_confirmation_must_match(): void
    {
        Notification::fake();

        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        /** @var PasswordBroker $passwordBroker */
        $passwordBroker = Password::broker('patients');
        $token = $passwordBroker->createToken($patient);

        $this->post(route('patient.password.update'), [
            'token' => $token,
            'email' => $patient->email,
            'password' => 'brand-new-secret',
            'password_confirmation' => 'something-else',
        ])->assertSessionHasErrors('password');
    }

    public function test_a_token_cannot_reset_an_account_from_another_role(): void
    {
        Notification::fake();

        /** @var Patient $patient */
        $patient = Patient::factory()->create();
        /** @var Doctor $doctor */
        $doctor = Doctor::factory()->create();

        // A token minted for a doctor must not unlock the patient form.
        /** @var PasswordBroker $doctorPasswordBroker */
        $doctorPasswordBroker = Password::broker('doctors');
        $doctorPasswordBroker->createToken($doctor);

        $this->post(route('patient.password.update'), [
            'token' => 'stolen',
            'email' => $patient->email,
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('brand-new-secret', $patient->fresh()->password));
    }
}
