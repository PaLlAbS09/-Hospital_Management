<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Support\GoogleMaps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesHospitalSchema;
use Tests\TestCase;

class ClinicProfileUpdateTest extends TestCase
{
    use CreatesHospitalSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createHospitalSchema();
    }

    /**
     * The details form always posts the full set of fields.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function details(array $overrides = []): array
    {
        return array_merge([
            'clinic_name' => 'Ortho-Med Diagnostic Center',
            'area' => 'Burdwan',
            'contact_number' => '9907215160',
        ], $overrides);
    }

    /* ---------------------------------------------------------------------
     | The update section
     * ------------------------------------------------------------------- */

    public function test_the_update_section_shows_the_current_details(): void
    {
        $clinic = Clinic::factory()->approved()->create([
            'clinic_name' => 'Ortho-Med Diagnostic Center',
            'area' => 'Burdwan',
            'contact_number' => '9907215160',
            'address' => 'Rakhal Pirtala',
        ]);

        $this->actingAs($clinic, 'clinic')
            ->get(route('clinic.profile.edit'))
            ->assertOk()
            ->assertSee('Ortho-Med Diagnostic Center')
            ->assertSee('Burdwan')
            ->assertSee('9907215160')
            ->assertSee('Rakhal Pirtala');
    }

    public function test_the_update_section_previews_the_map_before_saving(): void
    {
        $clinic = Clinic::factory()->approved()->create([
            'area' => 'Burdwan',
            'address' => 'Rakhal Pirtala',
        ]);

        $this->actingAs($clinic, 'clinic')
            ->get(route('clinic.profile.edit'))
            ->assertOk()
            ->assertSee(GoogleMaps::embedUrl('Rakhal Pirtala, Burdwan', 15));
    }

    /* ---------------------------------------------------------------------
     | Saving
     * ------------------------------------------------------------------- */

    public function test_clinic_can_update_its_contact_details(): void
    {
        $clinic = Clinic::factory()->approved()->create();

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.profile.update'), $this->details([
                'contact_number' => '9876543210',
                'area' => 'Kolkata',
            ]))
            ->assertSessionHas('success');

        $saved = $clinic->fresh();

        $this->assertSame('Ortho-Med Diagnostic Center', $saved->clinic_name);
        $this->assertSame('Kolkata', $saved->area);
        $this->assertSame('9876543210', $saved->contact_number);
    }

    public function test_clinic_can_update_its_address_and_coordinates(): void
    {
        $clinic = Clinic::factory()->approved()->create(['address' => null]);

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.profile.update'), $this->details([
                'address' => 'Rakhal Pirtala, Purba Bardhaman',
                'latitude' => '23.232403',
                'longitude' => '87.861506',
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clinics', [
            'clinic_id' => $clinic->clinic_id,
            'address' => 'Rakhal Pirtala, Purba Bardhaman',
        ]);

        $saved = $clinic->fresh();

        $this->assertSame(23.232403, $saved->latitude);
        $this->assertSame(87.861506, $saved->longitude);
        $this->assertSame('23.232403,87.861506', $saved->map_query);
    }

    public function test_leaving_the_coordinate_boxes_blank_falls_back_to_the_address(): void
    {
        $clinic = Clinic::factory()->approved()->create([
            'address' => 'Rakhal Pirtala',
            'latitude' => 23.232403,
            'longitude' => 87.861506,
        ]);

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.profile.update'), $this->details([
                'address' => 'Rakhal Pirtala',
                'latitude' => '',
                'longitude' => '',
            ]))
            ->assertSessionHas('success');

        $saved = $clinic->fresh();

        $this->assertNull($saved->latitude);
        $this->assertNull($saved->longitude);
        $this->assertSame('Rakhal Pirtala, '.$saved->area, $saved->map_query);
    }

    public function test_the_login_email_only_changes_when_a_new_one_is_submitted(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        $original = $clinic->email;

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.profile.update'), $this->details(['email' => '']))
            ->assertSessionHas('success');

        $this->assertSame($original, $clinic->fresh()->email);

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.profile.update'), $this->details(['email' => 'reception@example.com']))
            ->assertSessionHas('success');

        $this->assertSame('reception@example.com', $clinic->fresh()->email);
    }

    public function test_the_approval_status_cannot_be_changed_from_this_form(): void
    {
        $clinic = Clinic::factory()->approved()->create();

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.profile.update'), $this->details(['status' => Clinic::STATUS_REJECTED]))
            ->assertSessionHas('success');

        $this->assertSame(Clinic::STATUS_APPROVED, $clinic->fresh()->status);
    }

    /* ---------------------------------------------------------------------
     | Validation
     * ------------------------------------------------------------------- */

    public function test_the_details_are_validated(): void
    {
        $clinic = Clinic::factory()->approved()->create();
        $other = Clinic::factory()->approved()->create();

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.profile.update'), [
                'clinic_name' => 'No',
                'area' => '',
                'contact_number' => '123',
                'email' => $other->email,
                'address' => str_repeat('a', 256),
                'latitude' => '120',
                'longitude' => 'not-a-number',
            ])
            ->assertSessionHasErrors([
                'clinic_name', 'area', 'contact_number', 'email', 'address', 'latitude', 'longitude',
            ]);
    }

    public function test_a_clinic_can_keep_the_email_it_already_uses(): void
    {
        $clinic = Clinic::factory()->approved()->create();

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.profile.update'), $this->details(['email' => $clinic->email]))
            ->assertSessionHasNoErrors();
    }

    public function test_the_update_section_requires_a_signed_in_clinic(): void
    {
        // The framework's `login` route is aliased to the landing page.
        $this->get(route('clinic.profile.edit'))->assertRedirect(route('home'));
    }
}
