<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ItemType;
use App\Models\CatalogItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'title' => 'Smart Pharmacy Desktop POS & Inventory Suite',
                'slug' => 'smart-pharmacy-desktop-pos-inventory-suite',
                'item_type' => ItemType::PRODUCT,
                'short_description' => 'Robust offline-first desktop application with batch tracking, billing, and stock control.',
                'full_description' => 'The complete desktop workstation software for retail and clinical pharmacies. Functions seamlessly in zero-connectivity environments with local SQLite database storage, fast barcode scanning, automated invoice printing, and smart low-stock alerts. Features 1-device license enforcement and instant encrypted synchronization when internet returns.',
                'price' => 299.00,
                'cover_image_path' => 'catalog/desktop-pos.jpg',
                'is_active' => true,
            ],
            [
                'title' => 'Smart Pharmacy Mobile Dispense Assistant',
                'slug' => 'smart-pharmacy-mobile-dispense-assistant',
                'item_type' => ItemType::PRODUCT,
                'short_description' => 'Handheld Android & iOS tablet companion for aisle dispensing and stock audits.',
                'full_description' => 'Equip your floor staff with a mobile handheld device for real-time medicine lookups, shelf audits, prescription checkouts, and customer queuing management. Securely binds to registered handheld hardware with cryptographic authentication.',
                'price' => 149.00,
                'cover_image_path' => 'catalog/mobile-dispense.jpg',
                'is_active' => true,
            ],
            [
                'title' => 'Thermal Barcode & Label Hardware Kit',
                'slug' => 'thermal-barcode-label-hardware-kit',
                'item_type' => ItemType::PRODUCT,
                'short_description' => 'Plug-and-play high-speed thermal label printer and 2D DataMatrix omnidirectional scanner.',
                'full_description' => 'Certified hardware bundle pre-calibrated to interface with Smart Pharmacy desktop workstations. Prints moisture-resistant medicine vial labels, cautionary stickers, and instant patient dosage schedules.',
                'price' => 189.50,
                'cover_image_path' => 'catalog/hardware-kit.jpg',
                'is_active' => true,
            ],
            [
                'title' => 'Automated Cloud Database Backup & Disaster Recovery',
                'slug' => 'automated-cloud-database-backup',
                'item_type' => ItemType::SERVICE,
                'short_description' => 'AES-256 encrypted nightly cloud archive with 1-click disaster recovery.',
                'full_description' => 'Never lose sales transactions, customer debts, or inventory ledger histories. Smart Pharmacy automatically compresses, encrypts, and uploads incremental database snapshots to our private cloud vault with strict tamper verification and SHA-256 integrity validation.',
                'price' => 29.00,
                'cover_image_path' => 'catalog/cloud-backup.jpg',
                'is_active' => true,
            ],
            [
                'title' => 'Clinical Drug Interaction & Contraindication API',
                'slug' => 'clinical-drug-interaction-api',
                'item_type' => ItemType::SERVICE,
                'short_description' => 'Real-time cross-interaction and allergy alerts powered by national formulary data.',
                'full_description' => 'Protect patients by automatically checking newly entered prescriptions against active patient medications, known allergies, pregnancy warnings, and dosage thresholds.',
                'price' => 45.00,
                'cover_image_path' => 'catalog/drug-interaction.jpg',
                'is_active' => true,
            ],
            [
                'title' => '24/7 Priority Emergency SLA & Dedicated Technical Support',
                'slug' => 'priority-emergency-sla-support',
                'item_type' => ItemType::SERVICE,
                'short_description' => 'Guaranteed 15-minute response SLA with remote hardware & software diagnostic intervention.',
                'full_description' => 'Direct VIP phone and remote-desktop hotline to senior biomedical and software engineers for critical retail outages, audit inspections, and regulatory compliance queries.',
                'price' => 79.00,
                'cover_image_path' => 'catalog/sla-support.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            CatalogItem::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}
