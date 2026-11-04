<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PlatformType;
use App\Enums\VerificationStatus;
use App\Models\Pharmacy;
use App\Models\PharmacyBackup;
use App\Models\PharmacyLicense;
use App\Models\RegisteredDevice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LicensingSecurityApiTest extends TestCase
{
    use RefreshDatabase;

    protected Pharmacy $approvedPharmacy;
    protected PharmacyLicense $activeLicense;
    protected string $plainSecret = 'super-secret-test-key-12345';
    protected string $deviceUuid = 'hw_counter_001_uuid';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('local');

        // Create an approved pharmacy
        $this->approvedPharmacy = Pharmacy::create([
            'pharmacy_name' => 'Apex Test Pharmacy',
            'trade_license_number' => 'TL-TEST-99881',
            'pharmacy_address' => '123 Medical Way, Dhaka',
            'pharmacy_phone' => '+8801700112233',
            'map_location_url' => 'https://maps.google.com/?q=23.7,90.4',
            'owner_name' => 'Dr. Test Owner',
            'owner_address' => '456 Residence Lane, Dhaka',
            'owner_phone' => '+8801800112233',
            'owner_email' => 'owner@testpharmacy.com',
            'password' => 'SecurePass123!',
            'verification_status' => VerificationStatus::APPROVED,
        ]);

        // Create an active license bound to $this->deviceUuid
        $this->activeLicense = PharmacyLicense::create([
            'pharmacy_id' => $this->approvedPharmacy->id,
            'license_key' => 'PHARMA-TEST-AAAA-BBBB',
            'client_secret_hash' => Crypt::encryptString($this->plainSecret),
            'session_days_limit' => 30,
            'valid_from' => Carbon::now()->subDay(),
            'valid_until' => Carbon::now()->addDays(29),
            'bound_device_uuid' => $this->deviceUuid,
            'bound_device_name' => 'Counter-Terminal-1',
            'bound_platform' => PlatformType::DESKTOP,
            'last_validated_at' => Carbon::now(),
            'is_revoked' => false,
        ]);

        RegisteredDevice::create([
            'pharmacy_id' => $this->approvedPharmacy->id,
            'pharmacy_license_id' => $this->activeLicense->id,
            'device_uuid' => $this->deviceUuid,
            'device_name' => 'Counter-Terminal-1',
            'platform' => PlatformType::DESKTOP,
            'app_version' => '1.0.0',
            'ip_address' => '127.0.0.1',
            'last_seen_at' => Carbon::now(),
            'is_active' => true,
        ]);
    }

    /**
     * Helper to compute valid HMAC headers.
     */
    protected function generateHmacHeaders(
        string $clientId,
        string $secret,
        array $payload,
        ?int $timestamp = null
    ): array {
        $timestamp = $timestamp ?? time();
        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $clientId . $timestamp . $payloadJson, $secret);

        return [
            'X-Client-Id' => $clientId,
            'X-Timestamp' => (string) $timestamp,
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function test_valid_license_validation_succeeds_and_issues_session_token(): void
    {
        $payload = [
            'license_key' => $this->activeLicense->license_key,
            'device_uuid' => $this->deviceUuid,
            'device_name' => 'Counter-Terminal-1',
            'platform' => 'desktop',
            'app_version' => '1.2.0',
        ];

        $headers = $this->generateHmacHeaders(
            $this->activeLicense->id,
            $this->plainSecret,
            $payload
        );

        $response = $this->postJson('/api/v1/license/validate', $payload, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'pharmacy_name' => 'Apex Test Pharmacy',
                    'license_status' => 'ACTIVE',
                    'bound_device_uuid' => $this->deviceUuid,
                ],
            ]);

        $this->assertNotEmpty($response->json('data.session_token'));
        $this->assertGreaterThan(0, $response->json('data.session_days_remaining'));
    }

    public function test_first_time_license_validation_binds_device(): void
    {
        // Unbind the license to simulate first-time activation
        $this->activeLicense->update([
            'bound_device_uuid' => null,
            'bound_device_name' => null,
        ]);
        RegisteredDevice::truncate();

        $newDeviceUuid = 'hw_fresh_tablet_uuid_999';
        $payload = [
            'license_key' => $this->activeLicense->license_key,
            'device_uuid' => $newDeviceUuid,
            'device_name' => 'Dispense-Tablet-01',
            'platform' => 'mobile',
            'app_version' => '2.0.1',
        ];

        $headers = $this->generateHmacHeaders(
            $this->activeLicense->license_key,
            $this->plainSecret,
            $payload
        );

        $response = $this->postJson('/api/v1/license/validate', $payload, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'bound_device_uuid' => $newDeviceUuid,
                ],
            ]);

        $this->activeLicense->refresh();
        $this->assertEquals($newDeviceUuid, $this->activeLicense->bound_device_uuid);
        $this->assertEquals('Dispense-Tablet-01', $this->activeLicense->bound_device_name);

        $this->assertDatabaseHas('registered_devices', [
            'pharmacy_license_id' => $this->activeLicense->id,
            'device_uuid' => $newDeviceUuid,
            'device_name' => 'Dispense-Tablet-01',
            'is_active' => true,
        ]);
    }

    public function test_device_mismatch_is_strictly_rejected_with_403(): void
    {
        $differentDeviceUuid = 'hw_unauthorized_different_machine';
        $payload = [
            'license_key' => $this->activeLicense->license_key,
            'device_uuid' => $differentDeviceUuid,
            'device_name' => 'Intruder-Laptop',
            'platform' => 'desktop',
            'app_version' => '1.0.0',
        ];

        $headers = $this->generateHmacHeaders(
            $this->activeLicense->id,
            $this->plainSecret,
            $payload
        );

        $response = $this->postJson('/api/v1/license/validate', $payload, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'DEVICE_MISMATCH',
                'message' => 'This license is bound to another device. Contact support to transfer machines.',
            ]);
    }

    public function test_replay_attack_prevention_rejects_expired_timestamp(): void
    {
        $payload = [
            'license_key' => $this->activeLicense->license_key,
            'device_uuid' => $this->deviceUuid,
        ];

        // 301 seconds in the past (> 300 cutoff window)
        $expiredTimestamp = time() - 305;
        $headers = $this->generateHmacHeaders(
            $this->activeLicense->id,
            $this->plainSecret,
            $payload,
            $expiredTimestamp
        );

        $response = $this->postJson('/api/v1/license/validate', $payload, $headers);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'TIMESTAMP_EXPIRED',
            ]);
    }

    public function test_missing_hmac_headers_rejected_with_401(): void
    {
        $payload = [
            'license_key' => $this->activeLicense->license_key,
            'device_uuid' => $this->deviceUuid,
        ];

        // Missing X-Signature
        $response = $this->postJson('/api/v1/license/validate', $payload, [
            'X-Client-Id' => $this->activeLicense->id,
            'X-Timestamp' => (string) time(),
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'MISSING_AUTH_HEADERS',
            ]);
    }

    public function test_tampered_payload_or_signature_fails_verification(): void
    {
        $payload = [
            'license_key' => $this->activeLicense->license_key,
            'device_uuid' => $this->deviceUuid,
        ];

        $headers = $this->generateHmacHeaders(
            $this->activeLicense->id,
            $this->plainSecret,
            $payload
        );

        // Tamper with payload after signing
        $tamperedPayload = array_merge($payload, ['extra_field' => 'hacked']);

        $response = $this->postJson('/api/v1/license/validate', $tamperedPayload, $headers);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'INVALID_SIGNATURE',
            ]);
    }

    public function test_suspended_pharmacy_license_rejected(): void
    {
        $this->approvedPharmacy->update(['verification_status' => VerificationStatus::SUSPENDED]);

        $payload = [
            'license_key' => $this->activeLicense->license_key,
            'device_uuid' => $this->deviceUuid,
        ];

        $headers = $this->generateHmacHeaders(
            $this->activeLicense->id,
            $this->plainSecret,
            $payload
        );

        $response = $this->postJson('/api/v1/license/validate', $payload, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'PHARMACY_SUSPENDED',
            ]);
    }

    public function test_revoked_license_rejected(): void
    {
        $this->activeLicense->update(['is_revoked' => true]);

        $payload = [
            'license_key' => $this->activeLicense->license_key,
            'device_uuid' => $this->deviceUuid,
        ];

        $headers = $this->generateHmacHeaders(
            $this->activeLicense->id,
            $this->plainSecret,
            $payload
        );

        $response = $this->postJson('/api/v1/license/validate', $payload, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'LICENSE_REVOKED',
            ]);
    }

    public function test_secure_backup_upload_with_session_token_succeeds(): void
    {
        $token = $this->approvedPharmacy->createToken('test-device', ['backup:upload'])->plainTextToken;

        $fakeFile = UploadedFile::fake()->create('pharmacy_backup.sqlite.gz', 1024, 'application/gzip');

        $response = $this->withToken($token)->post('/api/v1/backup/upload', [
            'backup_file' => $fakeFile,
            'device_uuid' => $this->deviceUuid,
            'backup_notes' => 'Automated nightly sync before closing.',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Database backup safely archived.',
            ]);

        $this->assertNotEmpty($response->json('backup_id'));

        $this->assertDatabaseHas('pharmacy_backups', [
            'pharmacy_id' => $this->approvedPharmacy->id,
            'device_uuid' => $this->deviceUuid,
            'notes' => 'Automated nightly sync before closing.',
        ]);

        $backup = PharmacyBackup::where('pharmacy_id', $this->approvedPharmacy->id)->first();
        $this->assertNotNull($backup);
        $this->assertStringContainsString('backups/' . $this->approvedPharmacy->id, $backup->file_path);
    }

    public function test_backup_upload_rejects_unbound_mismatched_device(): void
    {
        $token = $this->approvedPharmacy->createToken('test-device', ['backup:upload'])->plainTextToken;

        $fakeFile = UploadedFile::fake()->create('unauthorized.sqlite', 512, 'application/x-sqlite3');

        $response = $this->withToken($token)->post('/api/v1/backup/upload', [
            'backup_file' => $fakeFile,
            'device_uuid' => 'hw_rogue_different_device_uuid',
            'backup_notes' => 'Rogue machine upload attempt',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'DEVICE_MISMATCH',
            ]);
    }
}
