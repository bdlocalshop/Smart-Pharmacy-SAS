<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\VerificationStatus;
use App\Models\Pharmacy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PharmacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pharmacies = [
            [
                'id' => '9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099',
                'pharmacy_name' => 'Apex Care Pharmacy',
                'trade_license_number' => 'TL-2026-88491-DHK',
                'pharmacy_address' => 'Plot 42, Block C, Gulshan Avenue, Dhaka 1212',
                'pharmacy_phone' => '+8801711002233',
                'map_location_url' => 'https://maps.google.com/?q=23.7925,90.4078',
                'owner_name' => 'Dr. Farhana Ahmed',
                'owner_address' => 'House 15, Road 27, Banani, Dhaka',
                'owner_phone' => '+8801819001122',
                'owner_email' => 'farhana.ahmed@apexpharmacy.com',
                'password' => Hash::make('Secret123!'),
                'verification_status' => VerificationStatus::APPROVED,
            ],
            [
                'id' => 'a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d',
                'pharmacy_name' => 'Green Cross Pharmaceuticals',
                'trade_license_number' => 'TL-2026-44012-CTG',
                'pharmacy_address' => '88 GEC Circle, Agrabad Commercial Area, Chattogram',
                'pharmacy_phone' => '+8801311554433',
                'map_location_url' => 'https://maps.google.com/?q=22.3569,91.7832',
                'owner_name' => 'Mohammad Rafiqul Islam',
                'owner_address' => 'Mirzarpool, Panchlaish, Chattogram',
                'owner_phone' => '+8801911776655',
                'owner_email' => 'rafiq.islam@greencross.com.bd',
                'password' => Hash::make('Secret123!'),
                'verification_status' => VerificationStatus::APPROVED,
            ],
            [
                'id' => 'b2c3d4e5-f6a7-8b9c-0d1e-2f3a4b5c6d7e',
                'pharmacy_name' => 'Metro Wellness Drugstore',
                'trade_license_number' => 'TL-2026-99381-SYL',
                'pharmacy_address' => 'Shop 12, Zindabazar Shopping Plaza, Sylhet',
                'pharmacy_phone' => '+8801511223344',
                'map_location_url' => 'https://maps.google.com/?q=24.8949,91.8687',
                'owner_name' => 'Syed Tanvir Hossain',
                'owner_address' => 'Shahjalal Upashahar, Sylhet',
                'owner_phone' => '+8801611334455',
                'owner_email' => 'tanvir@metrowellness.com',
                'password' => Hash::make('Secret123!'),
                'verification_status' => VerificationStatus::PENDING,
            ],
            [
                'id' => 'c3d4e5f6-a7b8-9c0d-1e2f-3a4b5c6d7e8f',
                'pharmacy_name' => 'St. Jude Community Pharmacy',
                'trade_license_number' => 'TL-2025-11029-RAJ',
                'pharmacy_address' => 'Station Road, Shaheb Bazar, Rajshahi',
                'pharmacy_phone' => '+8801712334455',
                'map_location_url' => 'https://maps.google.com/?q=24.3636,88.6241',
                'owner_name' => 'Nusrat Jahan',
                'owner_address' => 'Kazihata, Rajshahi',
                'owner_phone' => '+8801812445566',
                'owner_email' => 'nusrat@stjudepharma.org',
                'password' => Hash::make('Secret123!'),
                'verification_status' => VerificationStatus::SUSPENDED,
            ],
            [
                'id' => 'd4e5f6a7-b8c9-0d1e-2f3a-4b5c6d7e8f9a',
                'pharmacy_name' => 'QuickMed Express',
                'trade_license_number' => 'TL-2026-77810-KHL',
                'pharmacy_address' => 'KDA Avenue, Royal Moor, Khulna',
                'pharmacy_phone' => '+8801912556677',
                'map_location_url' => 'https://maps.google.com/?q=22.8456,89.5403',
                'owner_name' => 'Kamrul Hasan',
                'owner_address' => 'Boyra, Khulna',
                'owner_phone' => '+8801312667788',
                'owner_email' => 'kamrul@quickmedexpress.com',
                'password' => Hash::make('Secret123!'),
                'verification_status' => VerificationStatus::REJECTED,
            ],
        ];

        foreach ($pharmacies as $data) {
            Pharmacy::updateOrCreate(
                ['id' => $data['id']],
                $data
            );
        }
    }
}
