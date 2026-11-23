<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlatformType;
use App\Models\PharmacyLicense;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class PharmacyLicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $licenses = [
            [
                'id' => 'e5f6a7b8-c9d0-1e2f-3a4b-5c6d7e8f9a0b',
                'pharmacy_id' => '9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099', // Apex Care Pharmacy
                'license_key' => 'PHARMA-8F92-BC10-44A1',
                'client_secret_hash' => Crypt::encryptString('apex-secret-key-2026'),
                'session_days_limit' => 30,
                'valid_from' => Carbon::now()->subDays(2),
                'valid_until' => Carbon::now()->addDays(28),
                'bound_device_uuid' => 'hw_f8a9201bce471d2839b',
                'bound_device_name' => 'Counter-PC-01',
                'bound_platform' => PlatformType::DESKTOP,
                'last_validated_at' => Carbon::now()->subMinutes(15),
                'is_revoked' => false,
            ],
            [
                'id' => 'f6a7b8c9-d0e1-2f3a-4b5c-6d7e8f9a0b1c',
                'pharmacy_id' => 'a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d', // Green Cross Pharmaceuticals
                'license_key' => 'PHARMA-44A1-CC88-99EF',
                'client_secret_hash' => Crypt::encryptString('greencross-secret-key-2026'),
                'session_days_limit' => 60,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addDays(60),
                'bound_device_uuid' => null, // Unbound, waiting for first device validation
                'bound_device_name' => null,
                'bound_platform' => null,
                'last_validated_at' => null,
                'is_revoked' => false,
            ],
            [
                'id' => 'a7b8c9d0-e1f2-3a4b-5c6d-7e8f9a0b1c2d',
                'pharmacy_id' => 'c3d4e5f6-a7b8-9c0d-1e2f-3a4b5c6d7e8f', // St. Jude Community Pharmacy (Suspended)
                'license_key' => 'PHARMA-1122-3344-5566',
                'client_secret_hash' => Crypt::encryptString('stjude-secret-key-2026'),
                'session_days_limit' => 30,
                'valid_from' => Carbon::now()->subDays(60),
                'valid_until' => Carbon::now()->subDays(30), // Expired
                'bound_device_uuid' => 'hw_99a8b7c6d5e4321',
                'bound_device_name' => 'Dispense-Tab-01',
                'bound_platform' => PlatformType::MOBILE,
                'last_validated_at' => Carbon::now()->subDays(31),
                'is_revoked' => false,
            ],
        ];

        foreach ($licenses as $data) {
            PharmacyLicense::updateOrCreate(
                ['id' => $data['id']],
                $data
            );
        }
    }
}
