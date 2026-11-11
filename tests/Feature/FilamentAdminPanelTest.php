<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ItemType;
use App\Enums\PlatformType;
use App\Enums\VerificationStatus;
use App\Mail\PharmacyLicenseCredentialsMail;
use App\Models\CatalogItem;
use App\Models\Pharmacy;
use App\Models\PharmacyBackup;
use App\Models\PharmacyLicense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class FilamentAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('local');

        $this->adminUser = User::create([
            'name' => 'Filament Super Admin',
            'email' => 'pharmacy@bdlocalshop.com',
            'password' => 'AdminSecret123!',
        ]);
    }

    public function test_unauthenticated_users_are_redirected_from_admin_and_api_docs(): void
    {
        // Unauthenticated access to Filament admin panel pages must redirect to login
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/pharmacies')->assertRedirect('/admin/login');
        $this->get('/admin/catalog-items')->assertRedirect('/admin/login');
        $this->get('/admin/pharmacy-backups')->assertRedirect('/admin/login');
        $this->get('/admin/api-docs')->assertRedirect('/admin/login');
    }

    public function test_doc_route_aliases_redirect_to_gated_admin_api_docs(): void
    {
        $this->get('/admin/docs')->assertRedirect('/admin/api-docs');
        $this->get('/admin/api-reference')->assertRedirect('/admin/api-docs');
    }

    public function test_authenticated_admin_can_access_dashboard_and_api_docs(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin')
            ->assertSuccessful();

        $this->actingAs($this->adminUser)
            ->get('/admin/api-docs')
            ->assertSuccessful()
            ->assertSee('Gated Admin API Reference')
            ->assertSee('HMAC-SHA256');
    }

    public function test_authenticated_admin_can_access_pharmacy_and_catalog_and_backup_resources(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/pharmacies')
            ->assertSuccessful();

        $this->actingAs($this->adminUser)
            ->get('/admin/catalog-items')
            ->assertSuccessful();

        $this->actingAs($this->adminUser)
            ->get('/admin/pharmacy-backups')
            ->assertSuccessful();
    }

    public function test_pharmacy_approval_action_logic(): void
    {
        $pharmacy = Pharmacy::create([
            'pharmacy_name' => 'Pending Review Pharmacy',
            'trade_license_number' => 'TL-PENDING-001',
            'pharmacy_address' => '789 Central Ave',
            'pharmacy_phone' => '+8801999888777',
            'map_location_url' => 'https://maps.google.com/?q=23.8,90.4',
            'owner_name' => 'Dr. Reviewee',
            'owner_address' => '101 Owner St',
            'owner_phone' => '+8801888777666',
            'owner_email' => 'reviewee@test.com',
            'password' => 'Secret123!',
            'verification_status' => VerificationStatus::PENDING,
        ]);

        $this->assertEquals(VerificationStatus::PENDING, $pharmacy->verification_status);

        // Simulate admin approval with 45 session days
        $sessionDays = 45;
        $pharmacy->update(['verification_status' => VerificationStatus::APPROVED]);
        $license = PharmacyLicense::create([
            'pharmacy_id' => $pharmacy->id,
            'license_key' => 'PHARMA-TEST-APPROVED-KEY',
            'client_secret_hash' => Crypt::encryptString('admin-generated-secret-key'),
            'session_days_limit' => $sessionDays,
            'valid_from' => Carbon::now(),
            'valid_until' => Carbon::now()->addDays($sessionDays),
            'is_revoked' => false,
        ]);

        $pharmacy->refresh();
        $this->assertTrue($pharmacy->isApproved());
        $this->assertNotNull($pharmacy->activeLicense);
        $this->assertEquals(45, $pharmacy->activeLicense->session_days_limit);
        $this->assertEquals('PHARMA-TEST-APPROVED-KEY', $pharmacy->activeLicense->license_key);
        $this->assertEquals('admin-generated-secret-key', $pharmacy->activeLicense->getPlainSecret());
    }

    public function test_pharmacy_device_lock_reset_logic(): void
    {
        $pharmacy = Pharmacy::create([
            'pharmacy_name' => 'Bound Pharmacy',
            'trade_license_number' => 'TL-BOUND-001',
            'pharmacy_address' => 'Address',
            'pharmacy_phone' => '+8801700000000',
            'map_location_url' => 'https://maps.google.com/?q=23.8,90.4',
            'owner_name' => 'Owner',
            'owner_address' => 'Address',
            'owner_phone' => '+8801800000000',
            'owner_email' => 'bound@test.com',
            'password' => 'Secret123!',
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        $license = PharmacyLicense::create([
            'pharmacy_id' => $pharmacy->id,
            'license_key' => 'PHARMA-BOUND-KEY',
            'client_secret_hash' => Crypt::encryptString('secret'),
            'session_days_limit' => 30,
            'valid_from' => Carbon::now(),
            'valid_until' => Carbon::now()->addDays(30),
            'bound_device_uuid' => 'hw_machine_to_be_cleared',
            'bound_device_name' => 'Old-PC',
            'bound_platform' => PlatformType::DESKTOP,
            'is_revoked' => false,
        ]);

        $this->assertEquals('hw_machine_to_be_cleared', $license->bound_device_uuid);

        // Simulate admin Reset Device Lock
        $license->update([
            'bound_device_uuid' => null,
            'bound_device_name' => null,
            'bound_platform' => null,
        ]);

        $license->refresh();
        $this->assertNull($license->bound_device_uuid);
    }

    public function test_authenticated_admin_can_render_edit_and_view_pharmacy_pages(): void
    {
        $pharmacy = Pharmacy::create([
            'pharmacy_name' => 'Healing Hands Pharmacy',
            'trade_license_number' => 'TL-EDIT-TEST-001',
            'pharmacy_address' => '456 Health Way, Suite 10',
            'pharmacy_phone' => '+8801700112233',
            'map_location_url' => 'https://maps.google.com/?q=23.7,90.3',
            'owner_name' => 'Farhana Ahmed',
            'owner_phone' => '+8801819001122',
            'owner_email' => 'farhana@example.com',
            'password' => 'SecurePass123!',
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        $this->actingAs($this->adminUser)
            ->get("/admin/pharmacies/{$pharmacy->id}/edit")
            ->assertSuccessful()
            ->assertSee('Pharmacy Business Information');

        $this->actingAs($this->adminUser)
            ->get("/admin/pharmacies/{$pharmacy->id}")
            ->assertSuccessful()
            ->assertSee('Healing Hands Pharmacy');
    }

    public function test_license_credentials_mail_renders_properly(): void
    {
        $pharmacy = Pharmacy::create([
            'pharmacy_name' => 'Email Render Test Pharmacy',
            'trade_license_number' => 'TL-EMAIL-001',
            'pharmacy_address' => '123 Test St',
            'pharmacy_phone' => '+8801700112233',
            'owner_name' => 'Dr. Email Tester',
            'owner_phone' => '+8801800112233',
            'owner_email' => 'tester@example.com',
            'password' => 'SecurePass123!',
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        $license = PharmacyLicense::create([
            'pharmacy_id' => $pharmacy->id,
            'license_key' => 'PHARMA-TEST-EMAIL-01',
            'client_secret_hash' => Crypt::encryptString('plain_test_secret_123'),
            'session_days_limit' => 30,
            'valid_from' => Carbon::now(),
            'valid_until' => Carbon::now()->addDays(30),
            'is_revoked' => false,
        ]);

        $mailable = new PharmacyLicenseCredentialsMail($pharmacy, $license, 'plain_test_secret_123', false);
        $mailable->assertSeeInHtml('PHARMA-TEST-EMAIL-01');
        $mailable->assertSeeInHtml('plain_test_secret_123');
        $mailable->assertSeeInHtml('Email Render Test Pharmacy');
        $mailable->assertSeeInHtml('TL-EMAIL-001');
    }

    public function test_license_credentials_can_be_recreated_and_collected(): void
    {
        Mail::fake();

        $pharmacy = Pharmacy::create([
            'pharmacy_name' => 'Recreation Pharmacy',
            'trade_license_number' => 'TL-RECREATE-001',
            'pharmacy_address' => '456 Recreate Way',
            'pharmacy_phone' => '+8801700112233',
            'owner_name' => 'Dr. Recreator',
            'owner_phone' => '+8801800112233',
            'owner_email' => 'recreator@example.com',
            'password' => 'SecurePass123!',
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        $license = PharmacyLicense::create([
            'pharmacy_id' => $pharmacy->id,
            'license_key' => 'PHARMA-OLD-KEY-001',
            'client_secret_hash' => Crypt::encryptString('old_secret_123'),
            'session_days_limit' => 30,
            'valid_from' => Carbon::now(),
            'valid_until' => Carbon::now()->addDays(30),
            'bound_device_uuid' => 'hw_old_machine_to_unbind',
            'is_revoked' => false,
        ]);

        // 1. Verify admin can collect existing plain secret
        $this->assertEquals('old_secret_123', $license->getPlainSecret());

        // 2. Simulate recreate credentials action
        $newKey = 'PHARMA-NEW-KEY-999';
        $newSecret = 'brand_new_secret_456';

        $license->update([
            'license_key' => $newKey,
            'client_secret_hash' => Crypt::encryptString($newSecret),
            'bound_device_uuid' => null,
        ]);

        Mail::to($pharmacy->owner_email)->send(
            new PharmacyLicenseCredentialsMail($pharmacy, $license, $newSecret, true)
        );

        // Assert new credentials can be collected
        $license->refresh();
        $this->assertEquals($newKey, $license->license_key);
        $this->assertEquals($newSecret, $license->getPlainSecret());
        $this->assertNull($license->bound_device_uuid);

        // Assert email dispatched
        Mail::assertSent(PharmacyLicenseCredentialsMail::class, function ($mail) use ($pharmacy, $newKey, $newSecret) {
            return $mail->hasTo($pharmacy->owner_email) &&
                $mail->license->license_key === $newKey &&
                $mail->plainSecret === $newSecret &&
                $mail->isRegenerated === true;
        });
    }
}
