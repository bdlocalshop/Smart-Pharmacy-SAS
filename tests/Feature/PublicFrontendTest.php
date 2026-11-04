<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ItemType;
use App\Enums\VerificationStatus;
use App\Models\CatalogItem;
use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CatalogItem::create([
            'title' => 'Test Desktop POS',
            'slug' => 'test-desktop-pos',
            'item_type' => ItemType::PRODUCT,
            'short_description' => 'Reliable offline POS workstation.',
            'full_description' => 'Full specifications of the desktop POS workstation.',
            'price' => 199.00,
            'is_active' => true,
        ]);

        CatalogItem::create([
            'title' => 'Test Cloud Backup Service',
            'slug' => 'test-cloud-backup-service',
            'item_type' => ItemType::SERVICE,
            'short_description' => 'Encrypted snapshot vault service.',
            'full_description' => 'Full specifications of the cloud snapshot service.',
            'price' => 29.00,
            'is_active' => true,
        ]);
    }

    public function test_home_page_renders_storefront_and_catalog_items(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Smart Pharmacy')
            ->assertSee('Test Desktop POS')
            ->assertSee('Test Cloud Backup Service')
            ->assertSee('Products')
            ->assertSee('Workstations')
            ->assertSee('Cloud Services');
    }

    public function test_registration_page_renders_complete_form(): void
    {
        $response = $this->get('/register-pharmacy');

        $response->assertStatus(200)
            ->assertSee('Pharmacy Registration Application')
            ->assertSee('pharmacy_name')
            ->assertSee('trade_license_number')
            ->assertSee('pharmacy_phone')
            ->assertSee('map_location_url')
            ->assertSee('owner_name')
            ->assertSee('owner_email')
            ->assertSee('password');
    }

    public function test_registration_validation_fails_with_invalid_or_missing_inputs(): void
    {
        $response = $this->post('/register-pharmacy', [
            'pharmacy_name' => '',
            'trade_license_number' => '',
            'pharmacy_phone' => 'invalid-phone',
            'map_location_url' => 'not-a-valid-url',
            'owner_email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors([
            'pharmacy_name',
            'trade_license_number',
            'pharmacy_phone',
            'map_location_url',
            'owner_name',
            'owner_email',
            'password',
        ]);

        $this->assertDatabaseCount('pharmacies', 0);
    }

    public function test_registration_succeeds_and_redirects_to_status_tracker(): void
    {
        $payload = [
            'pharmacy_name' => 'Green Valley Pharmacy',
            'trade_license_number' => 'TL-2026-VALLEY-99',
            'pharmacy_address' => '12 Valley Road, Green Town',
            'pharmacy_phone' => '+8801711223344',
            'map_location_url' => 'https://maps.google.com/?q=23.77,90.41',
            'owner_name' => 'Tariq Al-Mansoor',
            'owner_address' => '45 Hilltop Residence',
            'owner_phone' => '+8801811556677',
            'owner_email' => 'tariq@greenvalley.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $response = $this->post('/register-pharmacy', $payload);

        $pharmacy = Pharmacy::where('trade_license_number', 'TL-2026-VALLEY-99')->first();

        $this->assertNotNull($pharmacy);
        $this->assertEquals(VerificationStatus::PENDING, $pharmacy->verification_status);

        $response->assertRedirect('/status?ref=' . $pharmacy->id)
            ->assertSessionHas('registration_success', true)
            ->assertSessionHas('registered_ref', $pharmacy->id);
    }

    public function test_registration_succeeds_without_optional_fields(): void
    {
        $payload = [
            'pharmacy_name' => 'Optional Fields Pharmacy',
            'trade_license_number' => 'TL-OPTIONAL-001',
            'pharmacy_address' => '404 No Map Street, Sector 7',
            'pharmacy_phone' => '+8801722334455',
            'owner_name' => 'Kamal Hossain',
            'owner_phone' => '+8801822334455',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $response = $this->post('/register-pharmacy', $payload);

        $pharmacy = Pharmacy::where('trade_license_number', 'TL-OPTIONAL-001')->first();

        $this->assertNotNull($pharmacy);
        $this->assertNull($pharmacy->map_location_url);
        $this->assertNull($pharmacy->owner_email);
        $this->assertNull($pharmacy->owner_address);
        $this->assertEquals(VerificationStatus::PENDING, $pharmacy->verification_status);

        $response->assertRedirect('/status?ref=' . $pharmacy->id);
    }

    public function test_status_page_looks_up_existing_pharmacy_and_displays_timeline(): void
    {
        $pharmacy = Pharmacy::create([
            'pharmacy_name' => 'Starlight Pharmacy',
            'trade_license_number' => 'TL-STAR-001',
            'pharmacy_address' => 'Starlight Ave',
            'pharmacy_phone' => '+8801700998877',
            'map_location_url' => 'https://maps.google.com/?q=23.8,90.4',
            'owner_name' => 'Sarah Connor',
            'owner_address' => 'Future Lane',
            'owner_phone' => '+8801800998877',
            'owner_email' => 'sarah@starlight.com',
            'password' => 'Secret123!',
            'verification_status' => VerificationStatus::PENDING,
        ]);

        $response = $this->get('/status?ref=' . $pharmacy->id);

        $response->assertStatus(200)
            ->assertSee('Starlight Pharmacy')
            ->assertSee('TL-STAR-001')
            ->assertSee('Pending');
    }

    public function test_pharmacy_directory_renders_active_subscribers_with_details_and_map_link(): void
    {
        $pharmacy = Pharmacy::create([
            'pharmacy_name' => 'Green Life Pharmacy',
            'trade_license_number' => 'TL-GREEN-2026',
            'pharmacy_address' => 'Plot 9, Road 11, Banani, Dhaka 1213',
            'pharmacy_phone' => '+8801700112233',
            'map_location_url' => 'https://maps.google.com/?q=23.7937,90.4066',
            'owner_name' => 'Dr. Rahman',
            'owner_address' => 'Banani, Dhaka',
            'owner_phone' => '+8801800112233',
            'owner_email' => 'rahman@greenlife.com',
            'password' => 'Secret123!',
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        PharmacyLicense::create([
            'pharmacy_id' => $pharmacy->id,
            'license_key' => 'PHARMA-GL-001',
            'client_secret_hash' => 'hash_secret',
            'session_days_limit' => 30,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
            'is_revoked' => false,
        ]);

        $response = $this->get('/pharmacies');

        $response->assertStatus(200)
            ->assertSee('Green Life Pharmacy')
            ->assertSee('Plot 9, Road 11, Banani, Dhaka 1213')
            ->assertSee('+8801700112233')
            ->assertSee('https://maps.google.com/?q=23.7937,90.4066')
            ->assertSee('target="_blank"', false)
            ->assertSee('Active');
    }

    public function test_pharmacy_directory_search_and_location_filtering(): void
    {
        $dhakaPharmacy = Pharmacy::create([
            'pharmacy_name' => 'Gulshan Drug Store',
            'trade_license_number' => 'TL-GUL-001',
            'pharmacy_address' => 'Gulshan 2, Dhaka',
            'pharmacy_phone' => '+8801711111111',
            'map_location_url' => 'https://maps.google.com/?q=23.79,90.41',
            'owner_name' => 'Owner 1',
            'owner_address' => 'Dhaka',
            'owner_phone' => '+8801811111111',
            'owner_email' => 'gulshan@example.com',
            'password' => 'Secret123!',
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        PharmacyLicense::create([
            'pharmacy_id' => $dhakaPharmacy->id,
            'license_key' => 'PHARMA-GUL-001',
            'client_secret_hash' => 'hash1',
            'session_days_limit' => 30,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
            'is_revoked' => false,
        ]);

        $ctgPharmacy = Pharmacy::create([
            'pharmacy_name' => 'Agrabad Meds',
            'trade_license_number' => 'TL-AGR-002',
            'pharmacy_address' => 'Agrabad Commercial Area, Chattogram',
            'pharmacy_phone' => '+8801722222222',
            'map_location_url' => 'https://maps.google.com/?q=22.32,91.81',
            'owner_name' => 'Owner 2',
            'owner_address' => 'Chattogram',
            'owner_phone' => '+8801822222222',
            'owner_email' => 'agrabad@example.com',
            'password' => 'Secret123!',
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        PharmacyLicense::create([
            'pharmacy_id' => $ctgPharmacy->id,
            'license_key' => 'PHARMA-AGR-002',
            'client_secret_hash' => 'hash2',
            'session_days_limit' => 30,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
            'is_revoked' => false,
        ]);

        // Search by name
        $searchResponse = $this->get('/pharmacies?search=Gulshan');
        $searchResponse->assertStatus(200)
            ->assertSee('Gulshan Drug Store')
            ->assertDontSee('Agrabad Meds');

        // Filter by location
        $locationResponse = $this->get('/pharmacies?location=Chattogram');
        $locationResponse->assertStatus(200)
            ->assertSee('Agrabad Meds')
            ->assertDontSee('Gulshan Drug Store');
    }

    public function test_pharmacy_directory_excludes_unapproved_or_expired_pharmacies(): void
    {
        // Pending pharmacy
        $pendingPharmacy = Pharmacy::create([
            'pharmacy_name' => 'Pending Pharmacy Corp',
            'trade_license_number' => 'TL-PEND-99',
            'pharmacy_address' => 'Mirpur, Dhaka',
            'pharmacy_phone' => '+8801799999999',
            'map_location_url' => 'https://maps.google.com/?q=23.80,90.36',
            'owner_name' => 'Pending Owner',
            'owner_address' => 'Dhaka',
            'owner_phone' => '+8801899999999',
            'owner_email' => 'pending@example.com',
            'password' => 'Secret123!',
            'verification_status' => VerificationStatus::PENDING,
        ]);

        // Approved but expired license
        $expiredPharmacy = Pharmacy::create([
            'pharmacy_name' => 'Expired License Pharmacy',
            'trade_license_number' => 'TL-EXP-99',
            'pharmacy_address' => 'Uttara, Dhaka',
            'pharmacy_phone' => '+8801788888888',
            'map_location_url' => 'https://maps.google.com/?q=23.87,90.39',
            'owner_name' => 'Expired Owner',
            'owner_address' => 'Dhaka',
            'owner_phone' => '+8801888888888',
            'owner_email' => 'expired@example.com',
            'password' => 'Secret123!',
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        PharmacyLicense::create([
            'pharmacy_id' => $expiredPharmacy->id,
            'license_key' => 'PHARMA-EXP-001',
            'client_secret_hash' => 'hash_exp',
            'session_days_limit' => 30,
            'valid_from' => now()->subDays(60),
            'valid_until' => now()->subDays(10), // expired
            'is_revoked' => false,
        ]);

        $response = $this->get('/pharmacies');

        $response->assertStatus(200)
            ->assertDontSee('Pending Pharmacy Corp')
            ->assertDontSee('Expired License Pharmacy');
    }
}
