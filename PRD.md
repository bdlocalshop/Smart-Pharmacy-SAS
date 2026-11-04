# Product Requirement Document (PRD)

**Project Name:** PharmaSync Engine & Cloud License Manager  
**Target Platform / AI Builder:** AntiGravity AI  
**Core Framework:** Laravel 11.x  
**Frontend Architecture:** Blade + Tailwind CSS + Alpine.js (Public Storefront & Registration)  
**Administration Panel:** Filament PHP v3  
**Database:** MySQL 8.x / PostgreSQL 15+  
**Target Clients:** Web Portal, Desktop Local Client (1 device/license), Mobile Local Client (1 device/license)

---

## 1. System Vision & Architecture Overview

PharmaSync serves as the centralized licensing, verification, and data backup backend for offline-first desktop and mobile pharmacy applications. The system consists of five tightly integrated layers:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                             LARAVEL 11 BACKEND                              │
├───────────────────────────────┬─────────────────────────────────────────────┤
│      PUBLIC FACING TIER       │             ADMIN & OPS TIER                │
│  • Blade + Tailwind Showcase  │  • Filament PHP v3 Panel                    │
│  • Pharmacy Onboarding Form   │  • Verification & Manual Review             │
│  • Status Tracking Portal     │  • License & Session Expiry Control         │
│                               │  • Gated API Documentation (Admin Only)     │
├───────────────────────────────┴─────────────────────────────────────────────┤
│                              API ENGINE TIER                                │
│  • HMAC SHA-256 + Device Fingerprint Authentication                        │
│  • Single-Device Enforcement Engine (1 License = 1 Active Device)           │
│  • Encrypted SQLite / JSON Backup Storage Endpoint                          │
└─────────────────────────────────────────────────────────────────────────────┘
                                  ▲           ▲
                Encrypted HTTPS   │           │   Encrypted HTTPS
                License & Backup  │           │   License & Backup
                                  │           │
         ┌────────────────────────┴──┐     ┌──┴────────────────────────┐
         │     Desktop Client        │     │       Mobile Client       │
         │ (Electron / Tauri / C#)   │     │   (Flutter / React Native)│
         └───────────────────────────┘     └───────────────────────────┘
```

---

## 2. Core Modules & Detailed Specifications

### Module 1: Public Storefront (Product & Service Showcase)
* **Design & UX:** Clean, high-performance UI built with Blade, Tailwind CSS, and Alpine.js.
* **Product Catalog:**
  * Grid display of products and digital pharmacy services.
  * Category filter, real-time search (Alpine.js driven), and detail modals or pages.
  * Each product contains: Name, SKU, category, highlighted badge, thumbnail gallery, detailed description, and pricing/inquiry button.
* **Navigation:** Responsive header with Home, Products, Services, Pharmacy Registration, and Status Check.

### Module 2: Pharmacy Self-Registration System
* **Form Inputs & Validations:**
  * **Pharmacy Information:**
    * `pharmacy_name` (String, required, max: 255)
    * `trade_license_number` (String, required, unique)
    * `pharmacy_address` (Text, required)
    * `pharmacy_phone` (String, required, regex validated for international/local format)
    * `map_location_url` (URL, required; Google Maps or OpenStreetMap pin link)
  * **Owner Information:**
    * `owner_name` (String, required)
    * `owner_address` (Text, required)
    * `owner_phone` (String, required)
    * `owner_email` (Email, required, unique)
  * **Authentication:**
    * `password` (Confirmed, min 8 characters)
* **Submission Workflow:**
  * Data persisted with default status: `pending`.
  * Flash notification and redirect to a live registration tracking page displaying application reference UUID and verification status.

### Module 3: Filament v3 Admin Management Panel
* **Protected Routes:** Gated by Filament authentication with super-admin role checks.
* **Pharmacy Verification Engine:**
  * List view filtering by: `pending`, `active`, `suspended`, `rejected`.
  * Detailed review view with map link preview embed/launcher.
  * Approval modal prompting:
    * **Initial Session Limit (in Days)** (Default: 30 days).
    * Automated generation of `license_key` (Format: `PHARMA-XXXX-XXXX-XXXX`) and `client_secret` (HMAC key).
* **License & Session Controller:**
  * Live remaining days counter.
  * Actions: **Extend Session (+N Days)**, **Freeze/Suspend**, **Reset Bound Device** (clears `device_uuid`).
* **Catalog Management (Products & Services):**
  * Filament Resource with schema: `title`, `slug`, `type` (Product vs. Service), `description`, `price`, `cover_image`, `is_active`.
* **Database Backup Repository:**
  * Filament view listing all incoming cloud backups per pharmacy with download link, file size, timestamp, and device fingerprint.

### Module 4: Gated Developer API Documentation
* **Security Rule:** **Strictly accessible only to authenticated Site Admins.**
* **Route:** `/admin/api-docs` (Embedded directly within Filament navigation).
* **Engine:** Integrated Scalar / Scribe interactive OpenAPI viewer.
* **Contents:**
  * Full interactive endpoints with "Try It Out" mode.
  * Cryptographic signing tutorial (generating HMAC signatures in JS, Dart, C#, Python).
  * Data payload models for external integrations.

---

## 3. Strict Single-Device License & Backup API

### 3.1 Security & Authentication Architecture
1. **Device Lock Rule:** Each approved pharmacy can strictly bind to **one physical device**. The first device to validate becomes permanently bound to the `device_uuid` until an Admin manually resets it in Filament.
2. **HMAC SHA-256 Signatures:** Every API call requires:
   * `X-Client-Id`: Assigned License ID / UUID
   * `X-Timestamp`: Current UTC UNIX Timestamp (±300s replay attack cutoff)
   * `X-Signature`: `hash_hmac('sha256', client_id . timestamp . payload_json, client_secret)`
3. **Rate Limiting:** Maximum 60 requests/minute per client.

### 3.2 Endpoint Specifications

#### 1. Validate License & Active Session
* **Method & Route:** `POST /api/v1/license/validate`
* **Request Headers:**
  ```http
  Content-Type: application/json
  X-Client-Id: 9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099
  X-Timestamp: 1730105000
  X-Signature: <calculated_sha256_hash>
  ```
* **Request Body:**
  ```json
  {
    "license_key": "PHARMA-8F92-BC10-44A1",
    "device_uuid": "hw_f8a9201bce471d2839b",
    "device_name": "Counter-PC-01",
    "platform": "desktop",
    "app_version": "1.2.0"
  }
  ```
* **Response (Success - 200 OK):**
  ```json
  {
    "status": "success",
    "data": {
      "pharmacy_name": "Apex Care Pharmacy",
      "license_status": "ACTIVE",
      "session_days_remaining": 28,
      "valid_until": "2026-11-25T23:59:59Z",
      "bound_device_uuid": "hw_f8a9201bce471d2839b",
      "session_token": "sanctum_plain_text_token"
    }
  }
  ```
* **Error Response - Device Mismatch (403 Forbidden):**
  ```json
  {
    "status": "error",
    "error_code": "DEVICE_MISMATCH",
    "message": "This license is bound to another device. Contact support to transfer machines."
  }
  ```

#### 2. Cloud Backup Upload Endpoint
Allows local desktop or mobile applications to push encrypted database snapshots (SQLite files or compressed JSON dumps) to the server.

* **Method & Route:** `POST /api/v1/client/backup`
* **Request Headers:** `Authorization: Bearer <session_token>`
* **Request Body (`multipart/form-data`):**
  * `backup_file`: File binary (`.sqlite`, `.sql.gz`, or `.json.gz`, max 50MB)
  * `device_uuid`: String
  * `backup_notes`: String (e.g., "Daily auto-sync before closing")
* **Server Action:**
  * Validates session validity and device match.
  * Stores file on encrypted private disk: `storage/app/backups/{pharmacy_id}/{timestamp}.gz`.
  * Logs transaction to `pharmacy_backups` table.
* **Response (201 Created):**
  ```json
  {
    "status": "success",
    "message": "Database backup safely archived.",
    "backup_id": "b18a4d2e-6f81-42cb-a212-32b00f894103",
    "timestamp": "2026-10-28T09:12:00Z"
  }
  ```

---

## 4. Complete Database Schema Blueprint

```sql
-- 1. Pharmacies & Owners Table
CREATE TABLE pharmacies (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    pharmacy_name VARCHAR(255) NOT NULL,
    trade_license_number VARCHAR(100) NOT NULL UNIQUE,
    pharmacy_address TEXT NOT NULL,
    pharmacy_phone VARCHAR(50) NOT NULL,
    map_location_url TEXT NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    owner_address TEXT NOT NULL,
    owner_phone VARCHAR(50) NOT NULL,
    owner_email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    verification_status ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- 2. Licensing & Device Binding Table (Single Device Rule)
CREATE TABLE pharmacy_licenses (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    pharmacy_id UUID NOT NULL,
    license_key VARCHAR(64) NOT NULL UNIQUE,
    client_secret_hash VARCHAR(255) NOT NULL,
    session_days_limit INT NOT NULL DEFAULT 30,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    bound_device_uuid VARCHAR(255) NULL,
    bound_device_name VARCHAR(150) NULL,
    bound_platform VARCHAR(50) NULL, -- 'desktop' or 'mobile'
    last_validated_at TIMESTAMP NULL,
    is_revoked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id) ON DELETE CASCADE
);

-- 3. Cloud Backups Table
CREATE TABLE pharmacy_backups (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    pharmacy_id UUID NOT NULL,
    device_uuid VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size_bytes BIGINT NOT NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id) ON DELETE CASCADE
);

-- 4. Products & Services Catalog
CREATE TABLE catalog_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    item_type ENUM('product', 'service') DEFAULT 'product',
    short_description TEXT NULL,
    full_description LONGTEXT NULL,
    price DECIMAL(10, 2) NULL,
    cover_image_path VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

---

## 5. Security & Antitamper Checklist

1. **Strict 1-Device Rule Enforcement:**
   * If `bound_device_uuid` is `NULL`, incoming `device_uuid` is saved on first successful validation.
   * If `bound_device_uuid` is set and does not match the payload, reject with HTTP `403 DEVICE_MISMATCH`.
   * Only admin action inside Filament can trigger "Reset Device Lock".
2. **Replay Protection:** Rejects any API call where `|current_timestamp - X-Timestamp| > 300` seconds.
3. **Secure Backups Isolation:** Cloud backups must never be placed in `/public`. Use Laravel's `Storage::disk('private')` and deliver files exclusively via authenticated, streamed Filament actions.
4. **Docs Access Protection:** Ensure the API docs route is registered with `middleware(['auth', 'can:access-filament'])`.

---

## 6. Implementation Phasing for AntiGravity AI

* **Phase 1 (Data & Admin Core):** Setup migrations, configure Filament v3, build Pharmacy Review Resource with Session Extension actions.
* **Phase 2 (Public Showcase & Registration):** Build Tailwind + Blade landing page, product gallery, and pharmacy application registration form.
* **Phase 3 (HMAC & Device API):** Implement middleware for signature verification, single device binding validation, and backup upload handlers.
* **Phase 4 (Docs & Hardening):** Integrate Scalar API documentation gated behind the Filament panel dashboard.