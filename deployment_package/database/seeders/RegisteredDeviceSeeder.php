<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlatformType;
use App\Models\RegisteredDevice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RegisteredDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = [
            [
                'id' => '11a2b3c4-d5e6-7f8a-9b0c-1d2e3f4a5b6c',
                'pharmacy_id' => '9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099', // Apex Care Pharmacy
                'pharmacy_license_id' => 'e5f6a7b8-c9d0-1e2f-3a4b-5c6d7e8f9a0b',
                'device_uuid' => 'hw_f8a9201bce471d2839b',
                'device_name' => 'Counter-PC-01',
                'platform' => PlatformType::DESKTOP,
                'app_version' => '1.2.0',
                'ip_address' => '103.114.98.22',
                'last_seen_at' => Carbon::now()->subMinutes(15),
                'is_active' => true,
            ],
            [
                'id' => '22b3c4d5-e6f7-8a9b-0c1d-2e3f4a5b6c7d',
                'pharmacy_id' => 'c3d4e5f6-a7b8-9c0d-1e2f-3a4b5c6d7e8f', // St. Jude Community Pharmacy
                'pharmacy_license_id' => 'a7b8c9d0-e1f2-3a4b-5c6d-7e8f9a0b1c2d',
                'device_uuid' => 'hw_99a8b7c6d5e4321',
                'device_name' => 'Dispense-Tab-01',
                'platform' => PlatformType::MOBILE,
                'app_version' => '1.1.4',
                'ip_address' => '103.114.98.50',
                'last_seen_at' => Carbon::now()->subDays(31),
                'is_active' => false,
            ],
        ];

        foreach ($devices as $data) {
            RegisteredDevice::updateOrCreate(
                ['id' => $data['id']],
                $data
            );
        }
    }
}
