<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ItemType;
use App\Enums\LicenseStatus;
use App\Enums\PlatformType;
use App\Enums\VerificationStatus;
use App\Models\CatalogItem;
use App\Models\Pharmacy;
use App\Models\PharmacyBackup;
use App\Models\PharmacyLicense;
use App\Models\RegisteredDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_user_seeded_with_uuid(): void
    {
        $admin = User::where('email', 'pharmacy@bdlocalshop.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue(Str::isUuid($admin->id));
        $this->assertEquals('System Administrator', $admin->name);
    }

    public function test_pharmacies_seeded_with_uuids_and_enums(): void
    {
        $pharmacy = Pharmacy::where('trade_license_number', 'TL-2026-88491-DHK')->first();

        $this->assertNotNull($pharmacy);
        $this->assertTrue(Str::isUuid($pharmacy->id));
        $this->assertEquals('Apex Care Pharmacy', $pharmacy->pharmacy_name);
        $this->assertEquals(VerificationStatus::APPROVED, $pharmacy->verification_status);
        $this->assertTrue($pharmacy->isApproved());
    }

    public function test_pharmacy_has_licenses_and_active_license(): void
    {
        $pharmacy = Pharmacy::where('trade_license_number', 'TL-2026-88491-DHK')->first();

        $this->assertNotNull($pharmacy);
        $this->assertCount(1, $pharmacy->licenses);

        $activeLicense = $pharmacy->activeLicense;
        $this->assertNotNull($activeLicense);
        $this->assertTrue(Str::isUuid($activeLicense->id));
        $this->assertEquals('PHARMA-8F92-BC10-44A1', $activeLicense->license_key);
        $this->assertEquals('hw_f8a9201bce471d2839b', $activeLicense->bound_device_uuid);
        $this->assertEquals(PlatformType::DESKTOP, $activeLicense->bound_platform);
        $this->assertEquals(LicenseStatus::ACTIVE, $activeLicense->status);
        $this->assertTrue($activeLicense->isValid());
        $this->assertGreaterThan(0, $activeLicense->days_remaining);
    }

    public function test_registered_devices_and_binding(): void
    {
        $device = RegisteredDevice::where('device_uuid', 'hw_f8a9201bce471d2839b')->first();

        $this->assertNotNull($device);
        $this->assertTrue(Str::isUuid($device->id));
        $this->assertEquals('Counter-PC-01', $device->device_name);
        $this->assertEquals(PlatformType::DESKTOP, $device->platform);
        $this->assertTrue($device->is_active);
        $this->assertNotNull($device->pharmacy);
        $this->assertNotNull($device->license);
        $this->assertEquals('Apex Care Pharmacy', $device->pharmacy->pharmacy_name);
    }

    public function test_pharmacy_backups_tracking(): void
    {
        $pharmacy = Pharmacy::where('trade_license_number', 'TL-2026-88491-DHK')->first();

        $this->assertNotNull($pharmacy);
        $this->assertCount(2, $pharmacy->backups);

        $backup = $pharmacy->backups->first();
        $this->assertNotNull($backup);
        $this->assertTrue(Str::isUuid($backup->id));
        $this->assertStringContainsString('MB', $backup->formatted_file_size);
    }

    public function test_catalog_items_and_scopes(): void
    {
        $total = CatalogItem::count();
        $this->assertGreaterThanOrEqual(6, $total);

        $products = CatalogItem::products()->get();
        $this->assertCount(3, $products);
        foreach ($products as $p) {
            $this->assertTrue(Str::isUuid($p->id));
            $this->assertEquals(ItemType::PRODUCT, $p->item_type);
        }

        $services = CatalogItem::services()->get();
        $this->assertCount(3, $services);
        foreach ($services as $s) {
            $this->assertTrue(Str::isUuid($s->id));
            $this->assertEquals(ItemType::SERVICE, $s->item_type);
        }
    }

    public function test_cascade_delete_removes_children(): void
    {
        $pharmacy = Pharmacy::where('trade_license_number', 'TL-2026-88491-DHK')->first();
        $pharmacyId = $pharmacy->id;

        $pharmacy->delete();

        $this->assertDatabaseMissing('pharmacies', ['id' => $pharmacyId]);
        $this->assertDatabaseMissing('pharmacy_licenses', ['pharmacy_id' => $pharmacyId]);
        $this->assertDatabaseMissing('registered_devices', ['pharmacy_id' => $pharmacyId]);
        $this->assertDatabaseMissing('pharmacy_backups', ['pharmacy_id' => $pharmacyId]);
    }
}
