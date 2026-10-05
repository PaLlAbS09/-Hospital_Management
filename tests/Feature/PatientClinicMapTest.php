<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use App\Support\GoogleMaps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesHospitalSchema;
use Tests\TestCase;

class PatientClinicMapTest extends TestCase
{
    use CreatesHospitalSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createHospitalSchema();
    }

    /* ---------------------------------------------------------------------
     | The map a patient sees on a clinic profile
     * ------------------------------------------------------------------- */

    public function test_clinic_profile_shows_its_map_and_a_directions_link(): void
    {
        $clinic = Clinic::factory()->approved()->create([
            'clinic_name' => 'Orthomed Diagnostic Center',
            'area' => 'Burdwan',
            'address' => 'Rakhal Pirtala',
        ]);

        $this->actingAs(Patient::factory()->create(), 'patient')
            ->get(route('patient.clinics.show', $clinic))
            ->assertOk()
            ->assertSee('Rakhal Pirtala')
            ->assertSee('Rakhal Pirtala, Burdwan')
            ->assertSee(GoogleMaps::embedUrl($clinic->map_query, 16))
            ->assertSee($clinic->directions_url);
    }

    public function test_a_clinic_without_a_street_address_is_still_mapped_by_its_area(): void
    {
        $clinic = Clinic::factory()->approved()->withoutAddress()->create(['area' => 'Siliguri']);

        $this->assertSame('Siliguri', $clinic->map_query);

        $this->actingAs(Patient::factory()->create(), 'patient')
            ->get(route('patient.clinics.show', $clinic))
            ->assertOk()
            ->assertSee(GoogleMaps::embedUrl('Siliguri', 16));
    }

    public function test_saved_coordinates_are_used_instead_of_the_address_text(): void
    {
        $clinic = Clinic::factory()->approved()->create([
            'address' => 'Rakhal Pirtala',
            'latitude' => 23.232403,
            'longitude' => 87.861506,
        ]);

        $this->assertStringStartsWith('23.232403', $clinic->map_query);
        $this->assertStringContainsString('87.861506', $clinic->map_query);
        $this->assertStringNotContainsString('Rakhal Pirtala', $clinic->map_query);

        $this->actingAs(Patient::factory()->create(), 'patient')
            ->get(route('patient.clinics.show', $clinic))
            ->assertOk()
            ->assertSee(urlencode($clinic->map_query));
    }

    public function test_the_embed_api_is_used_when_an_api_key_is_configured(): void
    {
        config(['services.google_maps.key' => 'test-embed-key']);

        $clinic = Clinic::factory()->approved()->create(['address' => 'Rakhal Pirtala']);

        $this->assertSame(
            'https://www.google.com/maps/embed/v1/place?'.http_build_query([
                'key' => 'test-embed-key',
                'q' => $clinic->map_query,
                'zoom' => 16,
                'hl' => 'en',
            ]),
            GoogleMaps::embedUrl($clinic->map_query, 16)
        );
    }

    public function test_the_about_page_no_longer_edits_the_address(): void
    {
        $clinic = Clinic::factory()->approved()->create(['address' => 'Rakhal Pirtala']);

        $this->actingAs($clinic, 'clinic')
            ->put(route('clinic.about.update'), [
                'address' => 'A different street',
                'latitude' => '23.232403',
                'longitude' => '87.861506',
            ])
            ->assertSessionHas('success');

        // The location lives on the clinic details page, so it is left alone here.
        $saved = $clinic->fresh();

        $this->assertSame('Rakhal Pirtala', $saved->address);
        $this->assertNull($saved->latitude);
    }
}
