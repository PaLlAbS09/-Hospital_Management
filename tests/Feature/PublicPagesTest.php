<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Clinic;
use App\Models\SystemQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesHospitalSchema;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use CreatesHospitalSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createHospitalSchema();
    }

    public function test_landing_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Book a doctor near you in three simple steps.');

        $html = (string) $response->getContent();

        $this->assertStringContainsString('<main', $html);
        $this->assertLessThan(
            strpos($html, 'Book a doctor near you in three simple steps.'),
            strpos($html, '<main'),
            'Landing page content should be rendered inside the layout <main> element.'
        );
    }

    public function test_clinic_directory_loads(): void
    {
        $this->get('/clinics')->assertOk();
    }

    public function test_contact_form_stores_query(): void
    {
        $this->post('/contact', [
            'user_name' => 'Jane Visitor',
            'email' => 'jane@example.com',
            'contact_number' => '9876543210',
            'message' => 'I would like to know more about the clinic network.',
        ])->assertRedirect(route('home').'#contact');

        $this->assertDatabaseHas('system_queries', [
            'email' => 'jane@example.com',
            'user_name' => 'Jane Visitor',
        ]);

        $this->assertSame(1, SystemQuery::count());
    }

    public function test_contact_form_rejects_invalid_payload(): void
    {
        $this->post('/contact', [
            'user_name' => '',
            'email' => 'not-an-email',
            'contact_number' => 'abc',
            'message' => 'short',
        ])->assertSessionHasErrors(['user_name', 'email', 'contact_number', 'message']);

        $this->assertSame(0, SystemQuery::count());
    }

    public function test_only_approved_clinics_are_listed_publicly(): void
    {
        Clinic::create([
            'clinic_name' => 'Hidden Clinic',
            'area' => 'Bardhaman',
            'email' => 'hidden@example.com',
            'password' => Hash::make('secret123'),
            'contact_number' => '9876543211',
            'status' => 'pending',
        ]);

        $response = $this->get('/clinics');
        $response->assertOk();
        $this->assertStringNotContainsString('Hidden Clinic', $response->getContent());
    }

    public function test_admin_login_screen_loads(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_patient_registration_screen_loads(): void
    {
        $this->get('/patient/register')->assertOk();
    }

    public function test_guest_cannot_reach_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect(route('home'));
    }

    public function test_admin_can_login_and_reach_dashboard(): void
    {
        Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')->assertOk();
    }
}
