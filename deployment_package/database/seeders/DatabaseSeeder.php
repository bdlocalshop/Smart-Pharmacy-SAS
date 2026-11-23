<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clean up legacy admin account if present
        User::where('email', 'admin@pharmasync.com')->delete();

        // Seed Administrator user for Filament Admin Panel
        User::updateOrCreate(
            ['email' => 'pharmacy@bdlocalshop.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('1Pharmacy12'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            PharmacySeeder::class,
            PharmacyLicenseSeeder::class,
            RegisteredDeviceSeeder::class,
            PharmacyBackupSeeder::class,
            CatalogItemSeeder::class,
        ]);
    }
}
