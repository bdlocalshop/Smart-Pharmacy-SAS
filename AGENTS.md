# AGENTS.md — Autonomous Agent Roles & Workflows

## Agent 1: System Architect & Database Lead
- **Scope:** Migrations, Eloquent Models, Relationships, Enums, UUID setup, and Database Seeders.
- **Rules:**
  - Enforce foreign keys and cascade rules.
  - Implement UUIDv4 for all primary keys.
  - Ensure strict typing (PHP 8.2+) on all models and relations.

## Agent 2: Security & Licensing Engine Specialist
- **Scope:** API Routing, HMAC-SHA256 signature middleware, Device fingerprint binding, Token rotation, Rate limiting.
- **Rules:**
  - Enforce 1-pharmacy = 1-device policy strictly. Reject any mismatch with HTTP 403 `DEVICE_MISMATCH`.
  - Check replay attacks on `X-Timestamp` (rejection window > 300 seconds).
  - Encrypt and store raw binary files securely for database backups.

## Agent 3: Filament v3 Admin Engineer
- **Scope:** Filament panels, custom actions, forms, tables, and metric widgets.
- **Rules:**
  - Create `PharmacyResource` with quick actions for Approve (assigning session days), Reject, Suspend, and Renew.
  - Create `CatalogItemResource` (Products & Services) with media upload.
  - Create `BackupResource` (view uploads, file size, download link).
  - Gate `/admin/api-docs` so only authenticated Filament admins can view it.

## Agent 4: Public Frontend Engineer (Blade + Tailwind)
- **Scope:** Public storefront, responsive product cards, category filters, and Pharmacy Registration form.
- **Rules:**
  - Use semantic HTML with Tailwind CSS and minimal Alpine.js for dropdowns/modals.
  - Ensure pharmacy registration form validates all business, owner, and map URL fields before submission.