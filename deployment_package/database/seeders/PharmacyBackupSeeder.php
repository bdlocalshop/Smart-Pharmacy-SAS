<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PharmacyBackup;
use Illuminate\Database\Seeder;

class PharmacyBackupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $backups = [
            [
                'id' => 'b18a4d2e-6f81-42cb-a212-32b00f894103',
                'pharmacy_id' => '9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099', // Apex Care Pharmacy
                'device_uuid' => 'hw_f8a9201bce471d2839b',
                'file_path' => 'backups/9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099/2026-10-27-daily-close.sqlite.gz',
                'file_size_bytes' => 14829104, // ~14.1 MB
                'notes' => 'Automatic daily close-of-business backup snapshot.',
            ],
            [
                'id' => 'c29b5e3f-7a92-43dc-b323-43c11a905214',
                'pharmacy_id' => '9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099', // Apex Care Pharmacy
                'device_uuid' => 'hw_f8a9201bce471d2839b',
                'file_path' => 'backups/9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099/2026-10-28-morning-sync.sqlite.gz',
                'file_size_bytes' => 15204352, // ~14.5 MB
                'notes' => 'Pre-opening automated database sync.',
            ],
        ];

        foreach ($backups as $data) {
            PharmacyBackup::updateOrCreate(
                ['id' => $data['id']],
                $data
            );
        }
    }
}
