-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: pharmacy_sas
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_items`
--

DROP TABLE IF EXISTS `catalog_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_items` (
  `id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `item_type` varchar(30) NOT NULL DEFAULT 'product',
  `short_description` text DEFAULT NULL,
  `full_description` longtext DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `cover_image_path` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catalog_items_slug_unique` (`slug`),
  KEY `catalog_items_item_type_index` (`item_type`),
  KEY `catalog_items_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_items`
--

LOCK TABLES `catalog_items` WRITE;
/*!40000 ALTER TABLE `catalog_items` DISABLE KEYS */;
INSERT INTO `catalog_items` VALUES ('a317bb5e-62b0-4f0e-875b-515bdf0d634b','PharmaSync Desktop POS & Inventory Suite','pharmasync-desktop-pos-inventory-suite','product','Robust offline-first desktop application with batch tracking, billing, and stock control.','The complete desktop workstation software for retail and clinical pharmacies. Functions seamlessly in zero-connectivity environments with local SQLite database storage, fast barcode scanning, automated invoice printing, and smart low-stock alerts. Features 1-device license enforcement and instant encrypted synchronization when internet returns.',299.00,'catalog/desktop-pos.jpg',1,'2026-10-28 15:33:51','2026-10-28 15:33:51'),('a317bb5e-807c-421b-84e4-e4346ccefb31','PharmaSync Mobile Dispense Assistant','pharmasync-mobile-dispense-assistant','product','Handheld Android & iOS tablet companion for aisle dispensing and stock audits.','Equip your floor staff with a mobile handheld device for real-time medicine lookups, shelf audits, prescription checkouts, and customer queuing management. Securely binds to registered handheld hardware with cryptographic authentication.',149.00,'catalog/mobile-dispense.jpg',1,'2026-10-28 15:33:51','2026-10-28 15:33:51'),('a317bb5f-e16a-4c5e-a15e-17f39dccf6df','Thermal Barcode & Label Hardware Kit','thermal-barcode-label-hardware-kit','product','Plug-and-play high-speed thermal label printer and 2D DataMatrix omnidirectional scanner.','Certified hardware bundle pre-calibrated to interface with Smart Pharmacy desktop workstations. Prints moisture-resistant medicine vial labels, cautionary stickers, and instant patient dosage schedules.',189.50,'catalog/hardware-kit.jpg',1,'2026-10-28 15:33:51','2026-10-28 21:27:41'),('a317bb5f-f800-4e7f-b094-040973ce5b93','Automated Cloud Database Backup & Disaster Recovery','automated-cloud-database-backup','service','AES-256 encrypted nightly cloud archive with 1-click disaster recovery.','Never lose sales transactions, customer debts, or inventory ledger histories. Smart Pharmacy automatically compresses, encrypts, and uploads incremental database snapshots to our private cloud vault with strict tamper verification and SHA-256 integrity validation.',29.00,'catalog/cloud-backup.jpg',1,'2026-10-28 15:33:52','2026-10-28 21:27:41'),('a317bb60-157d-43ea-9c87-79f1307197f3','Clinical Drug Interaction & Contraindication API','clinical-drug-interaction-api','service','Real-time cross-interaction and allergy alerts powered by national formulary data.','Protect patients by automatically checking newly entered prescriptions against active patient medications, known allergies, pregnancy warnings, and dosage thresholds.',45.00,'catalog/drug-interaction.jpg',1,'2026-10-28 15:33:52','2026-10-28 15:33:52'),('a317bb60-231e-4d74-803a-2d2b720985b0','24/7 Priority Emergency SLA & Dedicated Technical Support','priority-emergency-sla-support','service','Guaranteed 15-minute response SLA with remote hardware & software diagnostic intervention.','Direct VIP phone and remote-desktop hotline to senior biomedical and software engineers for critical retail outages, audit inspections, and regulatory compliance queries.',79.00,'catalog/sla-support.jpg',1,'2026-10-28 15:33:52','2026-10-28 15:33:52'),('a31839e7-dfa4-4e96-94b1-75778bbfdea6','Smart Pharmacy Desktop POS & Inventory Suite','smart-pharmacy-desktop-pos-inventory-suite','product','Robust offline-first desktop application with batch tracking, billing, and stock control.','The complete desktop workstation software for retail and clinical pharmacies. Functions seamlessly in zero-connectivity environments with local SQLite database storage, fast barcode scanning, automated invoice printing, and smart low-stock alerts. Features 1-device license enforcement and instant encrypted synchronization when internet returns.',299.00,'catalog/desktop-pos.jpg',1,'2026-10-28 21:27:40','2026-10-28 21:27:40'),('a31839e7-fa8a-48ee-bf73-d8877be4abd9','Smart Pharmacy Mobile Dispense Assistant','smart-pharmacy-mobile-dispense-assistant','product','Handheld Android & iOS tablet companion for aisle dispensing and stock audits.','Equip your floor staff with a mobile handheld device for real-time medicine lookups, shelf audits, prescription checkouts, and customer queuing management. Securely binds to registered handheld hardware with cryptographic authentication.',149.00,'catalog/mobile-dispense.jpg',1,'2026-10-28 21:27:40','2026-10-28 21:27:40');
/*!40000 ALTER TABLE `catalog_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_28_000001_create_pharmacies_table',1),(5,'2026_10_28_000002_create_pharmacy_licenses_table',1),(6,'2026_10_28_000003_create_registered_devices_table',1),(7,'2026_10_28_000004_create_pharmacy_backups_table',1),(8,'2026_10_28_000005_create_catalog_items_table',1),(9,'2026_10_28_094650_create_personal_access_tokens_table',1),(10,'2026_10_30_000001_make_non_mandatory_fields_nullable_in_pharmacies_table',2),(11,'2026_11_18_000001_add_opening_hours_to_pharmacies_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` char(36) NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\Pharmacy','9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099','device:hw_f8a9201bce471d2839b','04911e14ce36bc0f74218edf3a39ce56a6025708b7ee75d7292767aa91b2e21d','[\"backup:upload\",\"license:read\"]','2026-11-04 16:33:15',NULL,'2026-11-04 16:33:08','2026-11-04 16:33:15');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacies`
--

DROP TABLE IF EXISTS `pharmacies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmacies` (
  `id` char(36) NOT NULL,
  `pharmacy_name` varchar(255) NOT NULL,
  `trade_license_number` varchar(100) NOT NULL,
  `pharmacy_address` text NOT NULL,
  `pharmacy_phone` varchar(50) NOT NULL,
  `map_location_url` text DEFAULT NULL,
  `opening_hours` varchar(500) DEFAULT NULL,
  `owner_name` varchar(255) NOT NULL,
  `owner_address` text DEFAULT NULL,
  `owner_phone` varchar(50) NOT NULL,
  `owner_email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `verification_status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pharmacies_trade_license_number_unique` (`trade_license_number`),
  UNIQUE KEY `pharmacies_owner_email_unique` (`owner_email`),
  KEY `pharmacies_verification_status_index` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacies`
--

LOCK TABLES `pharmacies` WRITE;
/*!40000 ALTER TABLE `pharmacies` DISABLE KEYS */;
INSERT INTO `pharmacies` VALUES ('9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099','Apex Care Pharmacy','TL-2026-88491-DHK','Plot 42, Block C, Gulshan Avenue, Dhaka 1212','+8801711002233','https://maps.google.com/?q=23.7925,90.4078',NULL,'Dr. Farhana Ahmed','House 15, Road 27, Banani, Dhaka','+8801819001122','farhana.ahmed@apexpharmacy.com','$2y$12$HHq08plCd.mwJGedHm61MuVnYl8DsY2I70LIxnvTO9LaI684LcIjy','approved','2026-10-28 15:33:50','2026-10-28 15:33:50'),('a3271ced-68eb-43c2-9504-f01897fc12c9','Bismillah Pharmacy & Healthcare','LT-45634-786','357/2, South Kafrul, Kafrul, Dhaka 1206','+880 1717-000000','https://maps.google.com/?q=23.790405,90.379603','8:00AM-1:00PM, 2:00PM-5:00PM, 6:00PM-11:30PM','Ali Reza','Kafrul, Dhaka','+880 1717-000001','rezatrue@gmail.com','$2y$12$F84dabl4lnks9AKxHhL3YOHMB752QSinrpOwlEWs.0GDpcDvOg8Bi','approved','2026-11-05 07:04:03','2026-11-17 16:53:17');
/*!40000 ALTER TABLE `pharmacies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_backups`
--

DROP TABLE IF EXISTS `pharmacy_backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmacy_backups` (
  `id` char(36) NOT NULL,
  `pharmacy_id` char(36) NOT NULL,
  `device_uuid` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size_bytes` bigint(20) unsigned NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pharmacy_backups_pharmacy_id_foreign` (`pharmacy_id`),
  KEY `pharmacy_backups_device_uuid_index` (`device_uuid`),
  CONSTRAINT `pharmacy_backups_pharmacy_id_foreign` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_backups`
--

LOCK TABLES `pharmacy_backups` WRITE;
/*!40000 ALTER TABLE `pharmacy_backups` DISABLE KEYS */;
INSERT INTO `pharmacy_backups` VALUES ('a325e57c-789c-434d-ae44-e0c14d7dafd8','9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099','hw_f8a9201bce471d2839b','backups/9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099/20261104_223312_hw_f8a9201bce471d2839b.db',55,'Automated nightly cron upload test','2026-11-04 16:33:12','2026-11-04 16:33:12'),('a325e581-3b0f-40a3-b87e-9dab5b044c38','9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099','hw_f8a9201bce471d2839b','backups/9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099/20261104_223315_hw_f8a9201bce471d2839b.db',55,'Automated nightly cron upload test','2026-11-04 16:33:15','2026-11-04 16:33:15'),('b18a4d2e-6f81-42cb-a212-32b00f894103','9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099','hw_f8a9201bce471d2839b','backups/9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099/2026-10-27-daily-close.sqlite.gz',14829104,'Automatic daily close-of-business backup snapshot.','2026-10-28 15:33:50','2026-10-28 15:33:50'),('c29b5e3f-7a92-43dc-b323-43c11a905214','9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099','hw_f8a9201bce471d2839b','backups/9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099/2026-10-28-morning-sync.sqlite.gz',15204352,'Pre-opening automated database sync.','2026-10-28 15:33:50','2026-10-28 15:33:50');
/*!40000 ALTER TABLE `pharmacy_backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_licenses`
--

DROP TABLE IF EXISTS `pharmacy_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmacy_licenses` (
  `id` char(36) NOT NULL,
  `pharmacy_id` char(36) NOT NULL,
  `license_key` varchar(64) NOT NULL,
  `client_secret_hash` text NOT NULL,
  `session_days_limit` int(10) unsigned NOT NULL DEFAULT 30,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `bound_device_uuid` varchar(255) DEFAULT NULL,
  `bound_device_name` varchar(150) DEFAULT NULL,
  `bound_platform` varchar(50) DEFAULT NULL,
  `last_validated_at` timestamp NULL DEFAULT NULL,
  `is_revoked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pharmacy_licenses_license_key_unique` (`license_key`),
  KEY `pharmacy_licenses_pharmacy_id_foreign` (`pharmacy_id`),
  KEY `pharmacy_licenses_bound_device_uuid_index` (`bound_device_uuid`),
  KEY `pharmacy_licenses_is_revoked_index` (`is_revoked`),
  CONSTRAINT `pharmacy_licenses_pharmacy_id_foreign` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_licenses`
--

LOCK TABLES `pharmacy_licenses` WRITE;
/*!40000 ALTER TABLE `pharmacy_licenses` DISABLE KEYS */;
INSERT INTO `pharmacy_licenses` VALUES ('a3271ced-d289-44dc-bdaa-8ea826142044','a3271ced-68eb-43c2-9504-f01897fc12c9','PHARMA-NCCC-JPRW-KLIA','eyJpdiI6InEveW1GRUVEbGxkZXhvTHpBUDNWN0E9PSIsInZhbHVlIjoicGs1cE1rbzdPMzlXNUNBVlFYWDdndVRJems3b1pTVXpiOFg4QkVTZjZJWT0iLCJtYWMiOiJlYzYxYTA1ZjkzMDE5NTcwZDhhZTc4ZWI2ZmU2ZmM2YWFjNzY3MzdiOWRiN2QwZTgyMzMxZGNiYmFkY2MwYzA4IiwidGFnIjoiIn0=',30,'2026-11-05 13:04:03','2026-12-11 02:24:12','hw_1793445542060_20980110','DESKTOP-1HFH9HG','desktop','2026-11-10 20:24:12',0,'2026-11-05 07:04:04','2026-11-10 20:24:12'),('e5f6a7b8-c9d0-1e2f-3a4b-5c6d7e8f9a0b','9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099','PHARMA-8F92-BC10-44A1','eyJpdiI6InhaaUhaZWVoaXVTbjJVRnZWQTViamc9PSIsInZhbHVlIjoibjIxZHBMaFhwTUZrL203cjVLd044QW5NSWMxYjY4aGFqcXN6MllmbVNXUT0iLCJtYWMiOiIxMzYyNDY1MjliYjE5YWJhMDM4NDJiNjJhZjcwYjhjYmQ0ODUwMGJmY2RkY2U1NDE0NTI4MTQ1ZTI5NTdiZTljIiwidGFnIjoiIn0=',30,'2026-10-26 21:33:50','2026-11-06 01:42:30','hw_f8a9201bce471d2839b','Counter-PC-01','desktop','2026-11-04 16:33:08',0,'2026-10-28 15:33:50','2026-11-10 19:42:30');
/*!40000 ALTER TABLE `pharmacy_licenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registered_devices`
--

DROP TABLE IF EXISTS `registered_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registered_devices` (
  `id` char(36) NOT NULL,
  `pharmacy_id` char(36) NOT NULL,
  `pharmacy_license_id` char(36) NOT NULL,
  `device_uuid` varchar(255) NOT NULL,
  `device_name` varchar(150) DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `app_version` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `registered_devices_pharmacy_license_id_device_uuid_unique` (`pharmacy_license_id`,`device_uuid`),
  KEY `registered_devices_pharmacy_id_foreign` (`pharmacy_id`),
  KEY `registered_devices_device_uuid_index` (`device_uuid`),
  KEY `registered_devices_is_active_index` (`is_active`),
  CONSTRAINT `registered_devices_pharmacy_id_foreign` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registered_devices_pharmacy_license_id_foreign` FOREIGN KEY (`pharmacy_license_id`) REFERENCES `pharmacy_licenses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registered_devices`
--

LOCK TABLES `registered_devices` WRITE;
/*!40000 ALTER TABLE `registered_devices` DISABLE KEYS */;
INSERT INTO `registered_devices` VALUES ('11a2b3c4-d5e6-7f8a-9b0c-1d2e3f4a5b6c','9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099','e5f6a7b8-c9d0-1e2f-3a4b-5c6d7e8f9a0b','hw_f8a9201bce471d2839b','Front-Counter-PC-01','desktop','1.2.0','127.0.0.1','2026-11-04 16:33:08',1,'2026-10-28 15:33:50','2026-11-04 16:33:08'),('a3271ced-e389-44dc-bdaa-8ea826142099','a3271ced-68eb-43c2-9504-f01897fc12c9','a3271ced-d289-44dc-bdaa-8ea826142044','hw_1793445542060_20980110','DESKTOP-1HFH9HG','desktop','1.0.0','127.0.0.1','2026-11-10 20:24:12',1,'2026-11-10 20:24:12','2026-11-10 20:24:12');
/*!40000 ALTER TABLE `registered_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('02fTOLKr6x8Y8Vh3E79RIyVnhNhuq7PtCsHh7qTH','a317bb5a-0a37-4950-8242-d2679194e9fa','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiblNneTdBT3NWcUZNY1p6RExCdnE5d1hNOExTN0lHVHU0SDY0QlNNeCI7czo4OiJmaWxhbWVudCI7YToyOntzOjEzOiJub3RpZmljYXRpb25zIjthOjE6e3M6NzoibWFpbGJveCI7czo0MDoianE4d2xsdTkwaHAyRWRoaTc5cnNsMTF0aURvZUxOZnNxZHkyUjI1cCI7fXM6NjoidGFibGVzIjthOjM6e3M6NjQ6IjA0ZThhZTlmNDUyNzI5YzBmNTBmY2RhZWJhODQ5N2Y5YmRiZWE0MmQ4N2FmZTE0NDdmZmI4NmQ0NTVlYTk5YmYiO2E6MTp7czo3OiJjb2x1bW5zIjthOjU6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyMjoicGhhcm1hY3kucGhhcm1hY3lfbmFtZSI7czo1OiJsYWJlbCI7czo4OiJQaGFybWFjeSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTE6ImRldmljZV91dWlkIjtzOjU6ImxhYmVsIjtzOjE4OiJPcmlnaW4gRGV2aWNlIFVVSUQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJmb3JtYXR0ZWRfZmlsZV9zaXplIjtzOjU6ImxhYmVsIjtzOjEyOiJBcmNoaXZlIFNpemUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6Im5vdGVzIjtzOjU6ImxhYmVsIjtzOjU6Ik5vdGVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY3JlYXRlZF9hdCI7czo1OiJsYWJlbCI7czoxMToiVXBsb2FkZWQgQXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fX1zOjY0OiI2ZTViMmQ0MTliODJmZDdlZjljYzRkMGZkM2M4OTg2ZmZkYmI5NjcyZGFhZjkzMjgzYmEwYWIzMjM2NmYwZTIyIjthOjE6e3M6NzoiY29sdW1ucyI7YTo4OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6InBoYXJtYWN5X25hbWUiO3M6NToibGFiZWwiO3M6MTM6IlBoYXJtYWN5IE5hbWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjIwOiJ0cmFkZV9saWNlbnNlX251bWJlciI7czo1OiJsYWJlbCI7czoxMzoiVHJhZGUgTGljZW5zZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6Im93bmVyX25hbWUiO3M6NToibGFiZWwiO3M6NToiT3duZXIiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJ2ZXJpZmljYXRpb25fc3RhdHVzIjtzOjU6ImxhYmVsIjtzOjY6IlN0YXR1cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6Mjg6ImFjdGl2ZUxpY2Vuc2UuZGF5c19yZW1haW5pbmciO3M6NToibGFiZWwiO3M6MTI6IlNlc3Npb24gRGF5cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MzE6ImFjdGl2ZUxpY2Vuc2UuYm91bmRfZGV2aWNlX3V1aWQiO3M6NToibGFiZWwiO3M6MTE6IkRldmljZSBMb2NrIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE2OiJtYXBfbG9jYXRpb25fdXJsIjtzOjU6ImxhYmVsIjtzOjM6Ik1hcCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6NzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY3JlYXRlZF9hdCI7czo1OiJsYWJlbCI7czoxMzoiUmVnaXN0ZXJlZCBBdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fX19czo2NDoiMmVkYzEzMWFiOGU4MGU4OWQ4MTNkMWIzYzE1NTE0OTBhY2M3YWRiZTE3MmYyYmE3Mjg2NWZlMTc4YmQ5MDUyMCI7YToxOntzOjc6ImNvbHVtbnMiO2E6Njp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE2OiJjb3Zlcl9pbWFnZV9wYXRoIjtzOjU6ImxhYmVsIjtzOjk6IlRodW1ibmFpbCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NToidGl0bGUiO3M6NToibGFiZWwiO3M6NToiVGl0bGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6Iml0ZW1fdHlwZSI7czo1OiJsYWJlbCI7czo0OiJUeXBlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJwcmljZSI7czo1OiJsYWJlbCI7czo1OiJQcmljZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToiaXNfYWN0aXZlIjtzOjU6ImxhYmVsIjtzOjY6IkFjdGl2ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6ODoiQWRkZWQgT24iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO319fX19czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtzOjM2OiJhMzE3YmI1YS0wYTM3LTQ5NTAtODI0Mi1kMjY3OTE5NGU5ZmEiO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkUmlWZXpuam1VSnBDdEdabzk2by82dVJ2VUJNcXl1aGltUWxYLklQUTJTOTJpYUZEL01kQmkiO30=',1794871993),('1bjeuDByqrHtfaIp0kuJiHpra8eKW714jAoKGg3y','a317bb5a-0a37-4950-8242-d2679194e9fa','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo2OntzOjY6Il90b2tlbiI7czo0MDoicWdVZnl2YnV5M1dVVWxCRDNNWjFBeHF6T0NPeVJMT0ZsU3ZhSUNoOSI7czo4OiJmaWxhbWVudCI7YToyOntzOjEzOiJub3RpZmljYXRpb25zIjthOjE6e3M6NzoibWFpbGJveCI7czo0MDoicm5UVE1BYWg4THJQUXdyRW8xTFZpNHRXUExOalRJakdXVWVpaVBRRCI7fXM6NjoidGFibGVzIjthOjE6e3M6NjQ6IjZlNWIyZDQxOWI4MmZkN2VmOWNjNGQwZmQzYzg5ODZmZmRiYjk2NzJkYWFmOTMyODNiYTBhYjMyMzY2ZjBlMjIiO2E6MTp7czo3OiJjb2x1bW5zIjthOjg6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoicGhhcm1hY3lfbmFtZSI7czo1OiJsYWJlbCI7czoxMzoiUGhhcm1hY3kgTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MjA6InRyYWRlX2xpY2Vuc2VfbnVtYmVyIjtzOjU6ImxhYmVsIjtzOjEzOiJUcmFkZSBMaWNlbnNlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoib3duZXJfbmFtZSI7czo1OiJsYWJlbCI7czo1OiJPd25lciI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6InZlcmlmaWNhdGlvbl9zdGF0dXMiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyODoiYWN0aXZlTGljZW5zZS5kYXlzX3JlbWFpbmluZyI7czo1OiJsYWJlbCI7czoxMjoiU2Vzc2lvbiBEYXlzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czozMToiYWN0aXZlTGljZW5zZS5ib3VuZF9kZXZpY2VfdXVpZCI7czo1OiJsYWJlbCI7czoxMToiRGV2aWNlIExvY2siO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6Im1hcF9sb2NhdGlvbl91cmwiO3M6NToibGFiZWwiO3M6MzoiTWFwIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo3O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjEzOiJSZWdpc3RlcmVkIEF0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9fX19fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM5OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcmVnaXN0ZXItcGhhcm1hY3kiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7czozNjoiYTMxN2JiNWEtMGEzNy00OTUwLTgyNDItZDI2NzkxOTRlOWZhIjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJFJpVmV6bmptVUpwQ3RHWm85Nm8vNnVSdlVCTXF5dWhpbVFsWC5JUFEyUzkyaWFGRC9NZEJpIjt9',1794957029),('jXLqSfCDS7kL6RZIiFhCitOGlSee3kcnFzdYrDHz','a317bb5a-0a37-4950-8242-d2679194e9fa','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','YTo2OntzOjY6Il90b2tlbiI7czo0MDoidmdlNGZtU3RZRmZOVXU2Q1BSczFQUGpOVGlhbUJFNVFjN1loeU1yQiI7czo4OiJmaWxhbWVudCI7YToyOntzOjEzOiJub3RpZmljYXRpb25zIjthOjE6e3M6NzoibWFpbGJveCI7czo0MDoicXhZMzdpb3hBUzFsUUJzbUxpSExKWkhNRVdGc29aaFFTV2NRaGJrbSI7fXM6NjoidGFibGVzIjthOjI6e3M6NjQ6IjZlNWIyZDQxOWI4MmZkN2VmOWNjNGQwZmQzYzg5ODZmZmRiYjk2NzJkYWFmOTMyODNiYTBhYjMyMzY2ZjBlMjIiO2E6MTp7czo3OiJjb2x1bW5zIjthOjk6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoicGhhcm1hY3lfbmFtZSI7czo1OiJsYWJlbCI7czoxMzoiUGhhcm1hY3kgTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MjA6InRyYWRlX2xpY2Vuc2VfbnVtYmVyIjtzOjU6ImxhYmVsIjtzOjEzOiJUcmFkZSBMaWNlbnNlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoib3duZXJfbmFtZSI7czo1OiJsYWJlbCI7czo1OiJPd25lciI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6InZlcmlmaWNhdGlvbl9zdGF0dXMiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyODoiYWN0aXZlTGljZW5zZS5kYXlzX3JlbWFpbmluZyI7czo1OiJsYWJlbCI7czoxMjoiU2Vzc2lvbiBEYXlzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czozMToiYWN0aXZlTGljZW5zZS5ib3VuZF9kZXZpY2VfdXVpZCI7czo1OiJsYWJlbCI7czoxMToiRGV2aWNlIExvY2siO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6Im1hcF9sb2NhdGlvbl91cmwiO3M6NToibGFiZWwiO3M6MzoiTWFwIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo3O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJvcGVuaW5nX2hvdXJzIjtzOjU6ImxhYmVsIjtzOjEzOiJPcGVuaW5nIEhvdXJzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9aTo4O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjEzOiJSZWdpc3RlcmVkIEF0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9fX1zOjY0OiIyZWRjMTMxYWI4ZTgwZTg5ZDgxM2QxYjNjMTU1MTQ5MGFjYzdhZGJlMTcyZjJiYTcyODY1ZmUxNzhiZDkwNTIwIjthOjE6e3M6NzoiY29sdW1ucyI7YTo2OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6ImNvdmVyX2ltYWdlX3BhdGgiO3M6NToibGFiZWwiO3M6OToiVGh1bWJuYWlsIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJ0aXRsZSI7czo1OiJsYWJlbCI7czo1OiJUaXRsZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToiaXRlbV90eXBlIjtzOjU6ImxhYmVsIjtzOjQ6IlR5cGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6InByaWNlIjtzOjU6ImxhYmVsIjtzOjU6IlByaWNlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJpc19hY3RpdmUiO3M6NToibGFiZWwiO3M6NjoiQWN0aXZlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY3JlYXRlZF9hdCI7czo1OiJsYWJlbCI7czo4OiJBZGRlZCBPbiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fX19fX1zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2NhdGFsb2ctaXRlbXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7czozNjoiYTMxN2JiNWEtMGEzNy00OTUwLTgyNDItZDI2NzkxOTRlOWZhIjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJFJpVmV6bmptVUpwQ3RHWm85Nm8vNnVSdlVCTXF5dWhpbVFsWC5JUFEyUzkyaWFGRC9NZEJpIjt9',1795185952),('wljdKRrDeIZjfVqrkAjebhbKUa6FJhXUYDZ68HYc','a317bb5a-0a37-4950-8242-d2679194e9fa','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoic1RxcjJuSkhZRFhvRVA0Z09mZTlXZXZqem1JdEdEck5wYnJnTGxndSI7czo4OiJmaWxhbWVudCI7YToxOntzOjEzOiJub3RpZmljYXRpb25zIjthOjE6e3M6NzoibWFpbGJveCI7czo2NDoiOTllNDA1OGVmNThiOWU1NGMxYmMzYmYzMzk3YjUzNTYwZWEwNzE4MmU0NTI1OWZhYWNhN2FhZDM0YThkM2MyOCI7fX1zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794904421);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('a317bb5a-0a37-4950-8242-d2679194e9fa','System Administrator','pharmacy@bdlocalshop.com','2026-10-28 15:33:48','$2y$12$RiVeznjmUJpCtGZo96o/6uRvUBMqyuhimQlX.IPQ2S92iaFD/MdBi','C4nTvnvHSg99M17OWW3tkBXaWWbfirmlNLCuEG516f62Z6pUYD8oWaie0pj8','2026-10-28 15:33:48','2026-10-28 15:33:48');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-20 21:54:16
