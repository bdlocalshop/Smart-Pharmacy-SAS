# RULES.md — Engineering Standards & Hard Constraints

1. **Stack Conformance:**
   - Framework: Laravel 11.x
   - Admin Panel: Filament PHP v3
   - Web Frontend: Laravel Blade + Tailwind CSS (No React/Vue SPAs for frontend)
   - Database: PostgreSQL or MySQL (UUID primary keys)

2. **Licensing Integrity & Device Lock:**
   - A pharmacy license is strictly bound to ONE device UUID (`registered_devices`).
   - If an approved pharmacy tries to validate from a different `device_uuid`, abort with `DEVICE_MISMATCH`. Only admin can reset device bindings.

3. **API Security:**
   - Every client API request must pass through `VerifyHmacSignature` middleware.
   - All documentation routes (`/admin/docs` or `/admin/api-reference`) must be protected behind the `auth:admin` or `can:access-filament` gate.

4. **Backup Safety:**
   - Backup files received from Desktop/Mobile (`.sqlite`, `.sql`, `.zip`) must be saved under `storage/app/backups/{pharmacy_id}/` using private visibility.
   - Validate MIME types and maximum file size (50MB default).

5. **Code Quality:**
   - Always run `php artisan test` or pest validation when modifying core business logic.
   - Never expose raw API secret keys in JSON API responses after initial approval.