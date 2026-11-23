<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\PlatformType;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use App\Models\RegisteredDevice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PharmacyRegistrationApiController extends Controller
{
    /**
     * Register a new pharmacy directly from a local device application and bind hardware.
     */
    public function register(Request $request): JsonResponse
    {
        // If external API client omitted password_confirmation, mirror password so single-password payloads succeed
        if (! $request->has('password_confirmation') && $request->filled('password')) {
            $request->merge(['password_confirmation' => $request->input('password')]);
        }

        // Support both comma-separated string and array of slot strings from external clients
        if ($request->has('opening_hours') && is_array($request->input('opening_hours'))) {
            $request->merge([
                'opening_hours' => implode(', ', array_filter(array_map('trim', $request->input('opening_hours')))),
            ]);
        }

        $validated = $request->validate([
            // Pharmacy business information
            'pharmacy_name' => ['required', 'string', 'max:255'],
            'trade_license_number' => ['required', 'string', 'max:100', 'unique:pharmacies,trade_license_number'],
            'pharmacy_address' => ['required', 'string'],
            'pharmacy_phone' => ['required', 'string', 'max:50'],
            'map_location_url' => ['nullable', 'url', 'max:500'],
            'opening_hours' => ['nullable', 'string', 'max:500'],

            // Owner / Authorized pharmacist information
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_phone' => ['required', 'string', 'max:50'],
            'owner_email' => ['nullable', 'email', 'max:255', 'unique:pharmacies,owner_email'],
            'owner_address' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],

            // Device hardware binding parameters
            'device_uuid' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:150'],
            'platform' => ['nullable', 'string', 'in:desktop,mobile'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        $deviceUuid = trim($validated['device_uuid']);

        // Strict 1-Pharmacy = 1-Device Rule:
        // Ensure this hardware device is not already bound to another active or pending pharmacy.
        $existingBinding = PharmacyLicense::where('bound_device_uuid', $deviceUuid)
            ->where('is_revoked', false)
            ->whereHas('pharmacy', function ($query) {
                $query->whereIn('verification_status', [
                    VerificationStatus::APPROVED,
                    VerificationStatus::PENDING,
                ]);
            })
            ->first();

        if ($existingBinding) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'DEVICE_ALREADY_BOUND',
                'message' => 'This device hardware (UUID) is already bound to an existing pharmacy. In accordance with the 1-pharmacy = 1-device policy, each workstation may only be bound to a single pharmacy.',
            ], Response::HTTP_CONFLICT);
        }

        // 1. Create Pharmacy Record
        $pharmacy = Pharmacy::create([
            'pharmacy_name' => $validated['pharmacy_name'],
            'trade_license_number' => $validated['trade_license_number'],
            'pharmacy_address' => $validated['pharmacy_address'],
            'pharmacy_phone' => $validated['pharmacy_phone'],
            'map_location_url' => $validated['map_location_url'] ?? null,
            'opening_hours' => $validated['opening_hours'] ?? null,
            'owner_name' => $validated['owner_name'],
            'owner_phone' => $validated['owner_phone'],
            'owner_email' => $validated['owner_email'] ?? null,
            'owner_address' => $validated['owner_address'] ?? null,
            'password' => Hash::make($validated['password']),
            'verification_status' => VerificationStatus::PENDING,
        ]);

        // 2. Generate Cryptographic License Key & Shared Secret
        $licenseKey = 'PHARMA-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        $clientSecret = Str::random(32);
        $sessionDays = 30;
        $now = Carbon::now();

        $platform = isset($validated['platform']) && $validated['platform'] === 'mobile'
            ? PlatformType::MOBILE
            : PlatformType::DESKTOP;

        // 3. Immediately Bind Device Hardware to the License
        $license = PharmacyLicense::create([
            'pharmacy_id' => $pharmacy->id,
            'license_key' => $licenseKey,
            'client_secret_hash' => Crypt::encryptString($clientSecret),
            'session_days_limit' => $sessionDays,
            'valid_from' => $now,
            'valid_until' => $now->copy()->addDays($sessionDays),
            'bound_device_uuid' => $deviceUuid,
            'bound_device_name' => $validated['device_name'] ?? null,
            'bound_platform' => $platform,
            'last_validated_at' => null,
            'is_revoked' => false,
        ]);

        // 4. Record Registered Device
        RegisteredDevice::create([
            'pharmacy_id' => $pharmacy->id,
            'pharmacy_license_id' => $license->id,
            'device_uuid' => $deviceUuid,
            'device_name' => $validated['device_name'] ?? null,
            'platform' => $platform,
            'app_version' => $validated['app_version'] ?? null,
            'ip_address' => $request->ip(),
            'last_seen_at' => $now,
            'is_active' => true,
        ]);

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Pharmacy registered successfully and hardware device bound.',
            'data' => [
                'pharmacy_id' => $pharmacy->id,
                'pharmacy_name' => $pharmacy->pharmacy_name,
                'trade_license_number' => $pharmacy->trade_license_number,
                'opening_hours' => $pharmacy->opening_hours,
                'opening_hours_slots' => $pharmacy->opening_hours_slots,
                'verification_status' => $pharmacy->verification_status->value,
                'bound_device' => [
                    'device_uuid' => $deviceUuid,
                    'device_name' => $license->bound_device_name,
                    'platform' => $platform->value,
                    'bound_at' => $now->toIso8601String(),
                ],
                'credentials' => [
                    'license_key' => $licenseKey,
                    'client_secret' => $clientSecret,
                    'session_days_limit' => $sessionDays,
                    'valid_from' => $license->valid_from->toIso8601String(),
                    'valid_until' => $license->valid_until->toIso8601String(),
                    'instructions' => 'Save license_key and client_secret securely on the local device. Use them with device_uuid to generate HMAC-SHA256 signatures when calling /api/v1/license/validate.',
                ],
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Check registration and activation status from local device.
     */
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => ['nullable', 'string'],
            'pharmacy_id' => ['nullable', 'string'],
            'trade_license_number' => ['nullable', 'string'],
            'device_uuid' => ['required', 'string'],
        ]);

        $query = Pharmacy::with(['activeLicense', 'licenses']);

        if (! empty($validated['pharmacy_id'])) {
            $query->where('id', $validated['pharmacy_id']);
        } elseif (! empty($validated['license_key'])) {
            $query->whereHas('licenses', function ($q) use ($validated) {
                $q->where('license_key', $validated['license_key']);
            });
        } elseif (! empty($validated['trade_license_number'])) {
            $query->where('trade_license_number', $validated['trade_license_number']);
        } else {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'MISSING_IDENTIFIER',
                'message' => 'Please provide license_key, pharmacy_id, or trade_license_number to check status.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pharmacy = $query->first();

        if (! $pharmacy) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'PHARMACY_NOT_FOUND',
                'message' => 'No pharmacy found matching the provided identifier.',
            ], Response::HTTP_NOT_FOUND);
        }

        /** @var PharmacyLicense|null $license */
        $license = $pharmacy->activeLicense ?? $pharmacy->licenses()->latest()->first();

        // Check device hardware binding lock
        if ($license && $license->bound_device_uuid !== null && $license->bound_device_uuid !== $validated['device_uuid']) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'DEVICE_MISMATCH',
                'message' => 'This pharmacy license is bound to another hardware device. Mismatched workstations are rejected.',
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'pharmacy_id' => $pharmacy->id,
                'pharmacy_name' => $pharmacy->pharmacy_name,
                'trade_license_number' => $pharmacy->trade_license_number,
                'opening_hours' => $pharmacy->opening_hours,
                'opening_hours_slots' => $pharmacy->opening_hours_slots,
                'verification_status' => $pharmacy->verification_status->value,
                'is_approved' => $pharmacy->verification_status === VerificationStatus::APPROVED,
                'license' => $license ? [
                    'license_key' => $license->license_key,
                    'status' => $license->status->value,
                    'days_remaining' => $license->days_remaining,
                    'valid_until' => $license->valid_until?->toIso8601String(),
                    'bound_device_uuid' => $license->bound_device_uuid,
                    'is_device_matched' => $license->bound_device_uuid === $validated['device_uuid'],
                ] : null,
            ],
        ], Response::HTTP_OK);
    }
}
