<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use App\Models\RegisteredDevice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceAppRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_device_app_can_register_and_bind_hardware_successfully(): void
    {
        $payload = [
            'pharmacy_name' => 'Metro Cure Pharmacy',
            'trade_license_number' => 'TL-2026-METRO-01',
            'pharmacy_address' => 'Shop 5, Ground Floor, Central Plaza, Dhaka',
            'pharmacy_phone' => '+8801711998877',
            'map_location_url' => 'https://maps.google.com/?q=23.75,90.39',
            'owner_name' => 'Dr. Arif Chowdhury',
            'owner_phone' => '+8801811998877',
            'owner_email' => 'arif@metrocure.com',
            'owner_address' => 'Gulshan 2, Dhaka',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            'device_uuid' => 'hw_terminal_alpha_99',
            'device_name' => 'Cash-Counter-01',
            'platform' => 'desktop',
            'app_version' => '1.2.0',
        ];

        $response = $this->postJson('/api/v1/pharmacy/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Pharmacy registered successfully and hardware device bound.',
                'data' => [
                    'pharmacy_name' => 'Metro Cure Pharmacy',
                    'trade_license_number' => 'TL-2026-METRO-01',
                    'verification_status' => 'pending',
                    'bound_device' => [
                        'device_uuid' => 'hw_terminal_alpha_99',
                        'device_name' => 'Cash-Counter-01',
                        'platform' => 'desktop',
                    ],
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertNotEmpty($responseData['credentials']['license_key']);
        $this->assertNotEmpty($responseData['credentials']['client_secret']);

        // Verify in database
        $pharmacy = Pharmacy::where('trade_license_number', 'TL-2026-METRO-01')->first();
        $this->assertNotNull($pharmacy);
        $this->assertEquals(VerificationStatus::PENDING, $pharmacy->verification_status);

        $license = PharmacyLicense::where('pharmacy_id', $pharmacy->id)->first();
        $this->assertNotNull($license);
        $this->assertEquals('hw_terminal_alpha_99', $license->bound_device_uuid);
        $this->assertEquals('Cash-Counter-01', $license->bound_device_name);
        $this->assertEquals($responseData['credentials']['license_key'], $license->license_key);

        $registeredDevice = RegisteredDevice::where('pharmacy_id', $pharmacy->id)->first();
        $this->assertNotNull($registeredDevice);
        $this->assertEquals('hw_terminal_alpha_99', $registeredDevice->device_uuid);
        $this->assertTrue($registeredDevice->is_active);
    }

    public function test_registration_alias_endpoint_works(): void
    {
        $payload = [
            'pharmacy_name' => 'Alias Cure Pharmacy',
            'trade_license_number' => 'TL-2026-ALIAS-01',
            'pharmacy_address' => 'Road 1, Banani',
            'pharmacy_phone' => '+8801700000001',
            'owner_name' => 'Salma Begum',
            'owner_phone' => '+8801800000001',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            'device_uuid' => 'hw_alias_machine_1',
        ];

        $response = $this->postJson('/api/v1/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'trade_license_number' => 'TL-2026-ALIAS-01',
                    'bound_device' => [
                        'device_uuid' => 'hw_alias_machine_1',
                    ],
                ],
            ]);
    }

    public function test_registration_fails_when_device_uuid_is_missing(): void
    {
        $payload = [
            'pharmacy_name' => 'Incomplete Pharmacy',
            'trade_license_number' => 'TL-NO-DEVICE-01',
            'pharmacy_address' => 'Some street',
            'pharmacy_phone' => '+8801700000002',
            'owner_name' => 'Kamal',
            'owner_phone' => '+8801800000002',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            // device_uuid intentionally omitted
        ];

        $response = $this->postJson('/api/v1/pharmacy/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device_uuid']);
    }

    public function test_registration_rejects_duplicate_trade_license(): void
    {
        Pharmacy::create([
            'pharmacy_name' => 'Existing Pharmacy',
            'trade_license_number' => 'TL-EXISTING-99',
            'pharmacy_address' => 'Existing road',
            'pharmacy_phone' => '+8801700990099',
            'owner_name' => 'Existing Owner',
            'owner_phone' => '+8801800990099',
            'password' => bcrypt('Password123!'),
            'verification_status' => VerificationStatus::PENDING,
        ]);

        $payload = [
            'pharmacy_name' => 'Duplicate TL Pharmacy',
            'trade_license_number' => 'TL-EXISTING-99',
            'pharmacy_address' => 'Some address',
            'pharmacy_phone' => '+8801700000003',
            'owner_name' => 'Owner',
            'owner_phone' => '+8801800000003',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            'device_uuid' => 'hw_brand_new_machine',
        ];

        $response = $this->postJson('/api/v1/pharmacy/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['trade_license_number']);
    }

    public function test_registration_enforces_strict_one_device_one_pharmacy_policy(): void
    {
        // First registration binds hardware 'hw_exclusive_pos_machine'
        $firstPayload = [
            'pharmacy_name' => 'First Pharmacy',
            'trade_license_number' => 'TL-FIRST-01',
            'pharmacy_address' => 'Address 1',
            'pharmacy_phone' => '+8801700000010',
            'owner_name' => 'Owner 1',
            'owner_phone' => '+8801800000010',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            'device_uuid' => 'hw_exclusive_pos_machine',
        ];

        $firstResponse = $this->postJson('/api/v1/pharmacy/register', $firstPayload);
        $firstResponse->assertStatus(201);

        // Second registration attempts to use the SAME hardware device
        $secondPayload = [
            'pharmacy_name' => 'Second Pharmacy Attempting Hijack',
            'trade_license_number' => 'TL-SECOND-02',
            'pharmacy_address' => 'Address 2',
            'pharmacy_phone' => '+8801700000020',
            'owner_name' => 'Owner 2',
            'owner_phone' => '+8801800000020',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            'device_uuid' => 'hw_exclusive_pos_machine', // Duplicate hardware!
        ];

        $secondResponse = $this->postJson('/api/v1/pharmacy/register', $secondPayload);

        $secondResponse->assertStatus(409)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'DEVICE_ALREADY_BOUND',
            ]);
    }

    public function test_device_app_can_check_status_via_api(): void
    {
        $regResponse = $this->postJson('/api/v1/pharmacy/register', [
            'pharmacy_name' => 'Status Check Pharmacy',
            'trade_license_number' => 'TL-STATUS-01',
            'pharmacy_address' => 'Address 3',
            'pharmacy_phone' => '+8801700000030',
            'owner_name' => 'Owner 3',
            'owner_phone' => '+8801800000030',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            'device_uuid' => 'hw_status_check_device',
        ]);

        $regResponse->assertStatus(201);
        $licenseKey = $regResponse->json('data.credentials.license_key');

        // Check status with matching device_uuid
        $statusResponse = $this->getJson("/api/v1/pharmacy/status?license_key={$licenseKey}&device_uuid=hw_status_check_device");

        $statusResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'pharmacy_name' => 'Status Check Pharmacy',
                    'trade_license_number' => 'TL-STATUS-01',
                    'verification_status' => 'pending',
                    'is_approved' => false,
                    'license' => [
                        'license_key' => $licenseKey,
                        'bound_device_uuid' => 'hw_status_check_device',
                        'is_device_matched' => true,
                    ],
                ],
            ]);
    }

    public function test_status_check_rejects_unauthorized_mismatched_device(): void
    {
        $regResponse = $this->postJson('/api/v1/pharmacy/register', [
            'pharmacy_name' => 'Mismatch Target Pharmacy',
            'trade_license_number' => 'TL-MISMATCH-01',
            'pharmacy_address' => 'Address 4',
            'pharmacy_phone' => '+8801700000040',
            'owner_name' => 'Owner 4',
            'owner_phone' => '+8801800000040',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            'device_uuid' => 'hw_legitimate_machine',
        ]);

        $licenseKey = $regResponse->json('data.credentials.license_key');

        // Impostor device tries checking status
        $statusResponse = $this->getJson("/api/v1/pharmacy/status?license_key={$licenseKey}&device_uuid=hw_impostor_machine");

        $statusResponse->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'DEVICE_MISMATCH',
            ]);
    }

    public function test_registered_device_can_signin_after_approval_and_rejects_other_devices(): void
    {
        $deviceUuid = 'hw_pos_register_alpha_1';

        $regResponse = $this->postJson('/api/v1/pharmacy/register', [
            'pharmacy_name' => 'Authorized POS Pharmacy',
            'trade_license_number' => 'TL-AUTH-POS-01',
            'pharmacy_address' => 'Road 12, Banani',
            'pharmacy_phone' => '+8801700000050',
            'owner_name' => 'Owner 5',
            'owner_phone' => '+8801800000050',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            'device_uuid' => $deviceUuid,
            'device_name' => 'Front-POS',
            'platform' => 'desktop',
            'app_version' => '1.2.0',
        ]);

        $regResponse->assertStatus(201);
        $licenseKey = $regResponse->json('data.credentials.license_key');
        $clientSecret = $regResponse->json('data.credentials.client_secret');
        $pharmacyId = $regResponse->json('data.pharmacy_id');

        // Before admin approval, license validation fails with PHARMACY_NOT_APPROVED
        $timestamp = time();
        $payload = [
            'license_key' => $licenseKey,
            'device_uuid' => $deviceUuid,
            'device_name' => 'Front-POS',
            'platform' => 'desktop',
            'app_version' => '1.2.0',
        ];
        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', "{$licenseKey}{$timestamp}{$payloadJson}", $clientSecret);

        $pendingValidation = $this->postJson('/api/v1/license/validate', $payload, [
            'X-Client-Id' => $licenseKey,
            'X-Timestamp' => (string) $timestamp,
            'X-Signature' => $signature,
        ]);

        $pendingValidation->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'PHARMACY_NOT_APPROVED',
            ]);

        // Admin approves the pharmacy
        $pharmacy = Pharmacy::find($pharmacyId);
        $pharmacy->update(['verification_status' => VerificationStatus::APPROVED]);

        // Now validation from the SIGNUP DEVICE succeeds
        $validValidation = $this->postJson('/api/v1/license/validate', $payload, [
            'X-Client-Id' => $licenseKey,
            'X-Timestamp' => (string) $timestamp,
            'X-Signature' => $signature,
        ]);

        $validValidation->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'pharmacy_name' => 'Authorized POS Pharmacy',
                    'bound_device_uuid' => $deviceUuid,
                ],
            ]);

        $this->assertNotEmpty($validValidation->json('data.session_token'));

        // Validation from an UNBOUND DIFFERENT DEVICE is strictly rejected with 403 DEVICE_MISMATCH
        $mismatchedPayload = [
            'license_key' => $licenseKey,
            'device_uuid' => 'hw_counter_pc_impostor',
            'device_name' => 'Fake-Device',
            'platform' => 'desktop',
            'app_version' => '1.2.0',
        ];
        $mismatchedJson = json_encode($mismatchedPayload);
        $mismatchedSig = hash_hmac('sha256', "{$licenseKey}{$timestamp}{$mismatchedJson}", $clientSecret);

        $mismatchedValidation = $this->postJson('/api/v1/license/validate', $mismatchedPayload, [
            'X-Client-Id' => $licenseKey,
            'X-Timestamp' => (string) $timestamp,
            'X-Signature' => $mismatchedSig,
        ]);

        $mismatchedValidation->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'DEVICE_MISMATCH',
            ]);
    }

    public function test_registration_forces_json_response_even_without_accept_header_on_validation_failure(): void
    {
        // When an outside client (curl/postman/script) calls without Accept: application/json,
        // it must return 422 JSON instead of a 302 redirect to the homepage.
        $response = $this->call('POST', '/api/v1/pharmacy/register', [], [], [], [
            'HTTP_ACCEPT' => '*/*',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors'])
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_registration_succeeds_without_password_confirmation_field(): void
    {
        $payload = [
            'pharmacy_name' => 'Direct API Pharmacy',
            'trade_license_number' => 'TL-DIRECT-01',
            'pharmacy_address' => 'Road 1, Dhanmondi',
            'pharmacy_phone' => '+8801700112233',
            'owner_name' => 'Direct Owner',
            'owner_phone' => '+8801800112233',
            'password' => 'SafePass123!',
            // Notice: no password_confirmation
            'device_uuid' => 'hw_direct_01',
        ];

        $response = $this->postJson('/api/v1/pharmacy/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'pharmacy_name' => 'Direct API Pharmacy',
                ],
            ]);
    }

    public function test_registration_alias_without_v1_prefix_succeeds(): void
    {
        $payload = [
            'pharmacy_name' => 'Non V1 Pharmacy',
            'trade_license_number' => 'TL-NONV1-01',
            'pharmacy_address' => 'Road 2, Gulshan',
            'pharmacy_phone' => '+8801700112244',
            'owner_name' => 'Non V1 Owner',
            'owner_phone' => '+8801800112244',
            'password' => 'SafePass123!',
            'device_uuid' => 'hw_nonv1_01',
        ];

        $response = $this->postJson('/api/pharmacy/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'pharmacy_name' => 'Non V1 Pharmacy',
                ],
            ]);
    }
}
