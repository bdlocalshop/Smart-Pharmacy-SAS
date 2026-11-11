<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PlatformType;
use App\Enums\VerificationStatus;
use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerCredentialVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected Pharmacy $approvedPharmacy;
    protected PharmacyLicense $license;

    protected function setUp(): void
    {
        parent::setUp();

        $this->approvedPharmacy = Pharmacy::create([
            'pharmacy_name' => 'Care Plus Pharmacy',
            'trade_license_number' => 'TL-2026-CARE-01',
            'pharmacy_address' => 'House 10, Road 5, Dhanmondi, Dhaka',
            'pharmacy_phone' => '+8801700112233',
            'map_location_url' => 'https://maps.google.com/?q=23.75,90.38',
            'owner_name' => 'Dr. Tanvir Hasan',
            'owner_address' => 'Dhanmondi R/A, Dhaka',
            'owner_phone' => '+8801800112233',
            'owner_email' => 'tanvir@careplus.com',
            'password' => Hash::make('SecretPass123!'),
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        $this->license = PharmacyLicense::create([
            'pharmacy_id' => $this->approvedPharmacy->id,
            'license_key' => 'PHARMA-CARE-9901-XYZ1',
            'client_secret_hash' => Crypt::encryptString('super_confidential_hmac_secret_key_123'),
            'session_days_limit' => 30,
            'valid_from' => Carbon::now()->subDays(2),
            'valid_until' => Carbon::now()->addDays(28),
            'bound_device_uuid' => 'hw_old_counter_pc_uuid',
            'bound_device_name' => 'Counter-01-Old',
            'bound_platform' => PlatformType::DESKTOP,
            'is_revoked' => false,
        ]);
    }

    public function test_owner_can_verify_credentials_from_unbound_new_machine(): void
    {
        $newMachineUuid = 'hw_brand_new_laptop_uuid_999';

        $payload = [
            'identifier' => 'TL-2026-CARE-01',
            'password' => 'SecretPass123!',
            'device_uuid' => $newMachineUuid,
        ];

        $response = $this->postJson('/api/v1/pharmacy/verify-credentials', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Owner credentials verified successfully.',
                'data' => [
                    'pharmacy' => [
                        'pharmacy_name' => 'Care Plus Pharmacy',
                        'trade_license_number' => 'TL-2026-CARE-01',
                        'verification_status' => 'approved',
                        'is_approved' => true,
                    ],
                    'owner' => [
                        'name' => 'Dr. Tanvir Hasan',
                        'email' => 'tanvir@careplus.com',
                        'phone' => '+8801800112233',
                    ],
                    'license_summary' => [
                        'has_license' => true,
                        'license_key' => 'PHARMA-CARE-9901-XYZ1',
                    ],
                    'hardware_lock' => [
                        'is_locked_to_device' => true,
                        'bound_device_name' => 'Counter-01-Old',
                        'calling_device_uuid' => $newMachineUuid,
                        'is_calling_device_bound' => false,
                        'binding_required' => true,
                    ],
                ],
            ]);

        // CRITICAL SECURITY ASSERTIONS: secrets must NEVER be exposed
        $rawResponse = $response->getContent();
        $this->assertStringNotContainsString('super_confidential_hmac_secret_key_123', $rawResponse);
        $this->assertStringNotContainsString('client_secret', $rawResponse);
        $this->assertStringNotContainsString('client_secret_hash', $rawResponse);
        $this->assertStringNotContainsString('password', $rawResponse);
    }

    public function test_owner_can_verify_credentials_with_email_or_phone_or_license_key(): void
    {
        // 1. By owner email
        $resEmail = $this->postJson('/api/v1/pharmacy/verify-credentials', [
            'identifier' => 'tanvir@careplus.com',
            'password' => 'SecretPass123!',
        ]);
        $resEmail->assertStatus(200)->assertJsonPath('data.owner.name', 'Dr. Tanvir Hasan');

        // 2. By owner phone
        $resPhone = $this->postJson('/api/v1/pharmacy/verify-credentials', [
            'identifier' => '+8801800112233',
            'password' => 'SecretPass123!',
        ]);
        $resPhone->assertStatus(200)->assertJsonPath('data.owner.name', 'Dr. Tanvir Hasan');

        // 3. By license key
        $resLicense = $this->postJson('/api/v1/pharmacy/verify-credentials', [
            'identifier' => 'PHARMA-CARE-9901-XYZ1',
            'password' => 'SecretPass123!',
        ]);
        $resLicense->assertStatus(200)->assertJsonPath('data.pharmacy.trade_license_number', 'TL-2026-CARE-01');
    }

    public function test_verification_fails_with_invalid_password(): void
    {
        $response = $this->postJson('/api/v1/pharmacy/verify-credentials', [
            'identifier' => 'TL-2026-CARE-01',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'INVALID_CREDENTIALS',
            ]);
    }

    public function test_verification_fails_with_non_existent_identifier(): void
    {
        $response = $this->postJson('/api/v1/pharmacy/verify-credentials', [
            'identifier' => 'NON-EXISTENT-TL-999',
            'password' => 'SecretPass123!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'INVALID_CREDENTIALS',
            ]);
    }

    public function test_verification_fails_without_password_or_identifier(): void
    {
        $response = $this->postJson('/api/v1/pharmacy/verify-credentials', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_verification_fails_for_suspended_pharmacy(): void
    {
        $this->approvedPharmacy->update([
            'verification_status' => VerificationStatus::SUSPENDED,
        ]);

        $response = $this->postJson('/api/v1/pharmacy/verify-credentials', [
            'identifier' => 'TL-2026-CARE-01',
            'password' => 'SecretPass123!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'PHARMACY_SUSPENDED',
            ]);
    }

    public function test_bound_machine_evaluates_is_calling_device_bound_true(): void
    {
        $response = $this->postJson('/api/v1/pharmacy/verify-credentials', [
            'identifier' => 'TL-2026-CARE-01',
            'password' => 'SecretPass123!',
            'device_uuid' => 'hw_old_counter_pc_uuid', // Matches bound hardware
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.hardware_lock.is_calling_device_bound', true)
            ->assertJsonPath('data.hardware_lock.binding_required', false);
    }

    public function test_endpoint_aliases_work_correctly(): void
    {
        // 1. /api/v1/owner/verify-credentials
        $res1 = $this->postJson('/api/v1/owner/verify-credentials', [
            'identifier' => 'TL-2026-CARE-01',
            'password' => 'SecretPass123!',
        ]);
        $res1->assertStatus(200);

        // 2. /api/pharmacy/verify-credentials (unprefixed)
        $res2 = $this->postJson('/api/pharmacy/verify-credentials', [
            'identifier' => 'TL-2026-CARE-01',
            'password' => 'SecretPass123!',
        ]);
        $res2->assertStatus(200);

        // 3. /api/owner/verify-credentials (unprefixed)
        $res3 = $this->postJson('/api/owner/verify-credentials', [
            'identifier' => 'TL-2026-CARE-01',
            'password' => 'SecretPass123!',
        ]);
        $res3->assertStatus(200);
    }
}
