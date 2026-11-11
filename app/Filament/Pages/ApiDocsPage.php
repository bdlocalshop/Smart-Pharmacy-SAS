<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;

class ApiDocsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-code-bracket-square';

    protected static ?string $navigationLabel = 'Developer API Docs';

    protected static ?string $title = 'Developer API Reference & HMAC Guide';

    protected static ?string $slug = 'api-docs';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.api-docs';

    /**
     * Header actions for the page.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('openFullscreen')
                ->label('Open Fullscreen Docs')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(route('admin.api-docs.embed'))
                ->openUrlInNewTab(),
        ];
    }

    /**
     * Get OpenAPI 3.1 specification for Scalar documentation renderer.
     */
    public function getOpenApiSpec(): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Smart Pharmacy Engine & Cloud License Manager API',
                'version' => '1.2.0',
                'description' => "Centralized offline-first licensing verification and cloud snapshot backup engine for retail & clinical desktop and mobile applications.\n\n### HMAC-SHA256 Cryptographic Authentication\nEvery client API request must pass through the `VerifyHmacSignature` pipeline.\n- **X-Client-Id**: Assigned License ID (UUID) or License Key.\n- **X-Timestamp**: Current UTC UNIX epoch timestamp (rejection cutoff: ±300 seconds).\n- **X-Signature**: `hash_hmac('sha256', client_id . timestamp . payload_json, client_secret)`\n\n### Strict 1-Device Hardware Binding Rule\nLicenses are strictly bound to one device UUID upon initial activation. Mismatched machines are rejected with HTTP 403 `DEVICE_MISMATCH`.",
            ],
            'servers' => [
                [
                    'url' => url('/api/v1'),
                    'description' => 'Current Environment API Server',
                ],
            ],
            'paths' => [
                '/pharmacy/register' => [
                    'post' => [
                        'summary' => 'Local Device App Registration & Hardware Binding',
                        'description' => "Allows local desktop or mobile POS client apps to register a new pharmacy and immediately lock the license to the signup device hardware UUID.\n\nUpon registration, an initial cryptographic license is generated with the `device_uuid` permanently bound, and the plain `client_secret` is returned once for local encrypted storage.",
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => [
                                            'pharmacy_name',
                                            'trade_license_number',
                                            'pharmacy_address',
                                            'pharmacy_phone',
                                            'owner_name',
                                            'owner_phone',
                                            'password',
                                            'device_uuid',
                                        ],
                                        'properties' => [
                                            'pharmacy_name' => ['type' => 'string', 'example' => 'Evergreen Care Pharmacy'],
                                            'trade_license_number' => ['type' => 'string', 'example' => 'TL-2026-DHK-8891'],
                                            'pharmacy_address' => ['type' => 'string', 'example' => 'House 12, Road 4, Dhanmondi, Dhaka'],
                                            'pharmacy_phone' => ['type' => 'string', 'example' => '+8801711223344'],
                                            'map_location_url' => ['type' => 'string', 'nullable' => true, 'example' => 'https://maps.google.com/?q=23.7461,90.3742'],
                                            'owner_name' => ['type' => 'string', 'example' => 'Dr. Masud Rahman'],
                                            'owner_phone' => ['type' => 'string', 'example' => '+8801811556677'],
                                            'owner_email' => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'masud@evergreen.com'],
                                            'owner_address' => ['type' => 'string', 'nullable' => true, 'example' => 'Dhanmondi R/A, Dhaka'],
                                            'password' => ['type' => 'string', 'format' => 'password', 'example' => 'SecurePass123!'],
                                            'password_confirmation' => ['type' => 'string', 'format' => 'password', 'nullable' => true, 'example' => 'SecurePass123!'],
                                            'device_uuid' => ['type' => 'string', 'example' => 'hw_unique_station_01'],
                                            'device_name' => ['type' => 'string', 'example' => 'Front-Counter-PC-01'],
                                            'platform' => ['type' => 'string', 'enum' => ['desktop', 'mobile'], 'example' => 'desktop'],
                                            'app_version' => ['type' => 'string', 'example' => '1.2.0'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Pharmacy registered successfully and hardware device bound',
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            'status' => 'success',
                                            'message' => 'Pharmacy registered successfully and hardware device bound.',
                                            'data' => [
                                                'pharmacy_id' => '0192e21b-8f3e-7b19-918c-3e6f982a7b11',
                                                'pharmacy_name' => 'Evergreen Care Pharmacy',
                                                'trade_license_number' => 'TL-2026-DHK-8891',
                                                'verification_status' => 'pending',
                                                'bound_device' => [
                                                    'device_uuid' => 'hw_f8a9201bce471d2839b',
                                                    'device_name' => 'Front-Counter-PC-01',
                                                    'platform' => 'desktop',
                                                    'bound_at' => '2026-10-30T10:15:00+00:00',
                                                ],
                                                'credentials' => [
                                                    'license_key' => 'PHARMA-7X9Y-2K1M-8N4Q',
                                                    'client_secret' => 'a8b9c1d2e3f405162738495a6b7c8d9e',
                                                    'session_days_limit' => 30,
                                                    'valid_from' => '2026-10-30T10:15:00+00:00',
                                                    'valid_until' => '2026-11-29T10:15:00+00:00',
                                                    'instructions' => 'Save license_key and client_secret securely on the local device. Use them with device_uuid to generate HMAC-SHA256 signatures when calling /api/v1/license/validate.',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '409' => [
                                'description' => 'Device UUID already bound to another pharmacy (1-device = 1-pharmacy rule violation)',
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            'status' => 'error',
                                            'error_code' => 'DEVICE_ALREADY_BOUND',
                                            'message' => 'This device hardware (UUID) is already bound to an existing pharmacy. In accordance with the 1-pharmacy = 1-device policy, each workstation may only be bound to a single pharmacy.',
                                        ],
                                    ],
                                ],
                            ],
                            '422' => [
                                'description' => 'Validation error (missing fields or duplicate trade license/email)',
                            ],
                        ],
                    ],
                ],
                '/pharmacy/status' => [
                    'get' => [
                        'summary' => 'Check Application & Hardware Binding Status',
                        'description' => 'Allows local POS devices to query approval status and verify that their device UUID matches the bound hardware lock.',
                        'parameters' => [
                            [
                                'name' => 'device_uuid',
                                'in' => 'query',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                                'description' => 'Current machine hardware UUID to verify against binding lock',
                            ],
                            [
                                'name' => 'license_key',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                                'description' => 'License key issued during signup',
                            ],
                            [
                                'name' => 'pharmacy_id',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                                'description' => 'Pharmacy UUID issued during signup',
                            ],
                            [
                                'name' => 'trade_license_number',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                                'description' => 'Registered trade license number',
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Pharmacy application status details',
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            'status' => 'success',
                                            'data' => [
                                                'pharmacy_id' => '0192e21b-8f3e-7b19-918c-3e6f982a7b11',
                                                'pharmacy_name' => 'Evergreen Care Pharmacy',
                                                'trade_license_number' => 'TL-2026-DHK-8891',
                                                'verification_status' => 'approved',
                                                'is_approved' => true,
                                                'license' => [
                                                    'license_key' => 'PHARMA-7X9Y-2K1M-8N4Q',
                                                    'status' => 'ACTIVE',
                                                    'days_remaining' => 30,
                                                    'valid_until' => '2026-11-29T10:15:00+00:00',
                                                    'bound_device_uuid' => 'hw_f8a9201bce471d2839b',
                                                    'is_device_matched' => true,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '403' => [
                                'description' => 'Hardware mismatch (queried device_uuid does not match bound machine)',
                            ],
                            '404' => [
                                'description' => 'Pharmacy not found',
                            ],
                        ],
                    ],
                ],
                '/pharmacy/verify-credentials' => [
                    'post' => [
                        'summary' => 'Verify Owner Credentials from Unbound Machine (New Machine)',
                        'description' => "Enables an owner on a new or unbound device to authenticate and retrieve complete pharmacy and owner profiles without requiring HMAC signatures or prior hardware binding.\n\n### Security Guarantees:\n- **No secrets exposed**: `client_secret` and cryptographic keys are strictly excluded.\n- **Diagnostics returned**: Informs the application if the license is locked to another machine or ready to bind.",
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['password'],
                                        'properties' => [
                                            'identifier' => [
                                                'type' => 'string',
                                                'description' => 'Trade license number, owner email, owner phone, or license key',
                                                'example' => 'TL-2026-88491-DHK',
                                            ],
                                            'password' => [
                                                'type' => 'string',
                                                'format' => 'password',
                                                'description' => 'Owner account password created during registration',
                                                'example' => 'Secret123!',
                                            ],
                                            'device_uuid' => [
                                                'type' => 'string',
                                                'description' => 'Hardware UUID of the new/unbound machine (optional for diagnostic lock evaluation)',
                                                'example' => 'hw_new_terminal_9901',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Owner verified successfully. Returns profile and license summary without secrets.',
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            'status' => 'success',
                                            'message' => 'Owner credentials verified successfully.',
                                            'data' => [
                                                'pharmacy' => [
                                                    'id' => '9bf3c8e4-7d52-4a57-8be7-59b1dc0a1099',
                                                    'pharmacy_name' => 'Apex Care Pharmacy',
                                                    'trade_license_number' => 'TL-2026-88491-DHK',
                                                    'pharmacy_phone' => '+8801711002233',
                                                    'pharmacy_address' => 'Plot 42, Block C, Gulshan Avenue, Dhaka 1212',
                                                    'map_location_url' => 'https://maps.google.com/?q=23.7925,90.4078',
                                                    'verification_status' => 'approved',
                                                    'is_approved' => true,
                                                    'registered_at' => '2026-10-28T09:00:00Z',
                                                ],
                                                'owner' => [
                                                    'name' => 'Dr. Farhana Ahmed',
                                                    'email' => 'farhana.ahmed@apexpharmacy.com',
                                                    'phone' => '+8801819001122',
                                                    'address' => 'House 15, Road 27, Banani, Dhaka',
                                                ],
                                                'license_summary' => [
                                                    'has_license' => true,
                                                    'license_key' => 'PHARMA-8F92-BC10-44A1',
                                                    'status' => 'ACTIVE',
                                                    'session_days_remaining' => 28,
                                                    'valid_until' => '2026-11-25T21:33:50Z',
                                                ],
                                                'hardware_lock' => [
                                                    'is_locked_to_device' => true,
                                                    'bound_device_name' => 'Counter-PC-01',
                                                    'calling_device_uuid' => 'hw_new_terminal_9901',
                                                    'is_calling_device_bound' => false,
                                                    'binding_required' => true,
                                                    'notice' => 'This license is locked to another machine (Counter-PC-01). To activate this new machine, reset the device lock in Filament Admin or contact administrator.',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                'description' => 'Invalid owner credentials (incorrect password or unrecognized identifier)',
                            ],
                            '403' => [
                                'description' => 'Pharmacy suspended by administration',
                            ],
                            '422' => [
                                'description' => 'Missing required fields',
                            ],
                        ],
                    ],
                ],
                '/license/validate' => [
                    'post' => [
                        'summary' => 'Validate License & Bind Hardware Device',
                        'description' => 'Validates license key, verifies HMAC signature, binds new hardware if unbound, and issues an active session token.',
                        'parameters' => [
                            [
                                'name' => 'X-Client-Id',
                                'in' => 'header',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                                'description' => 'License UUID or License Key',
                            ],
                            [
                                'name' => 'X-Timestamp',
                                'in' => 'header',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                                'description' => 'Current UTC Unix timestamp (±300s window)',
                            ],
                            [
                                'name' => 'X-Signature',
                                'in' => 'header',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                                'description' => 'HMAC-SHA256 signature calculated with client_secret',
                            ],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['license_key', 'device_uuid'],
                                        'properties' => [
                                            'license_key' => ['type' => 'string', 'example' => 'PHARMA-8F92-BC10-44A1'],
                                            'device_uuid' => ['type' => 'string', 'example' => 'hw_f8a9201bce471d2839b'],
                                            'device_name' => ['type' => 'string', 'example' => 'Counter-PC-01'],
                                            'platform' => ['type' => 'string', 'enum' => ['desktop', 'mobile'], 'example' => 'desktop'],
                                            'app_version' => ['type' => 'string', 'example' => '1.2.0'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'License active & device bound successfully',
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            'status' => 'success',
                                            'data' => [
                                                'pharmacy_name' => 'Apex Care Pharmacy',
                                                'license_status' => 'ACTIVE',
                                                'session_days_remaining' => 28,
                                                'valid_until' => '2026-11-25T23:59:59Z',
                                                'bound_device_uuid' => 'hw_f8a9201bce471d2839b',
                                                'session_token' => '1|sanctum_plain_text_token',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '403' => [
                                'description' => 'Hardware mismatch or expired/suspended license',
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            'status' => 'error',
                                            'error_code' => 'DEVICE_MISMATCH',
                                            'message' => 'This license is bound to another device. Contact support to transfer machines.',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                'description' => 'HMAC signature verification failed or timestamp expired',
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            'status' => 'error',
                                            'error_code' => 'TIMESTAMP_EXPIRED',
                                            'message' => 'Request timestamp is outside the valid 300-second window.',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/backup/upload' => [
                    'post' => [
                        'summary' => 'Upload Encrypted Database Archive Snapshot',
                        'description' => 'Allows registered clients to push encrypted database backups (.sqlite, .sql.gz, .zip). Authenticated via Bearer session token or HMAC signature.',
                        'security' => [
                            ['BearerAuth' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'Authorization',
                                'in' => 'header',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                                'description' => 'Bearer <session_token>',
                            ],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'multipart/form-data' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['backup_file', 'device_uuid'],
                                        'properties' => [
                                            'backup_file' => [
                                                'type' => 'string',
                                                'format' => 'binary',
                                                'description' => 'Database snapshot file (max 50MB)',
                                            ],
                                            'device_uuid' => [
                                                'type' => 'string',
                                                'example' => 'hw_f8a9201bce471d2839b',
                                            ],
                                            'backup_notes' => [
                                                'type' => 'string',
                                                'example' => 'Daily auto-sync before closing',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Backup safely archived',
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            'status' => 'success',
                                            'message' => 'Database backup safely archived.',
                                            'backup_id' => 'b18a4d2e-6f81-42cb-a212-32b00f894103',
                                            'timestamp' => '2026-10-28T09:12:00Z',
                                        ],
                                    ],
                                ],
                            ],
                            '403' => [
                                'description' => 'Device mismatch against registered hardware lock',
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ],
                ],
            ],
        ];
    }
}
