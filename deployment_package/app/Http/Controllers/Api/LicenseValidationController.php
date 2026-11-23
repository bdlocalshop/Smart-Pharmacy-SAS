<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PharmacyLicense;
use App\Models\RegisteredDevice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LicenseValidationController extends Controller
{
    /**
     * Validate license, enforce strict single-device lock, and issue a session token.
     */
    public function validateLicense(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => ['required', 'string'],
            'device_uuid' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:150'],
            'platform' => ['nullable', 'string', 'in:desktop,mobile'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        /** @var PharmacyLicense $license */
        $license = $request->attributes->get('license');

        // Confirm the payload license_key corresponds to the authenticated license
        if ($license->license_key !== $validated['license_key']) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'LICENSE_KEY_MISMATCH',
                'message' => 'The provided license_key does not match the authenticated credential.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if license duration has expired
        if ($license->valid_until && Carbon::now()->greaterThanOrEqualTo($license->valid_until)) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'LICENSE_EXPIRED',
                'message' => 'This license session has expired. Contact support to renew your subscription.',
            ], Response::HTTP_FORBIDDEN);
        }

        $deviceUuid = $validated['device_uuid'];
        $deviceName = $validated['device_name'] ?? null;
        $platform = $validated['platform'] ?? null;
        $appVersion = $validated['app_version'] ?? null;

        // Strict 1-Device Rule Enforcement
        if ($license->bound_device_uuid === null) {
            // First time activation: bind device
            $license->update([
                'bound_device_uuid' => $deviceUuid,
                'bound_device_name' => $deviceName,
                'bound_platform' => $platform,
                'last_validated_at' => Carbon::now(),
            ]);

            RegisteredDevice::updateOrCreate(
                [
                    'pharmacy_license_id' => $license->id,
                    'device_uuid' => $deviceUuid,
                ],
                [
                    'pharmacy_id' => $license->pharmacy_id,
                    'device_name' => $deviceName,
                    'platform' => $platform,
                    'app_version' => $appVersion,
                    'ip_address' => $request->ip(),
                    'last_seen_at' => Carbon::now(),
                    'is_active' => true,
                ]
            );
        } elseif ($license->bound_device_uuid !== $deviceUuid) {
            // Rejection: Mismatched device attempting validation
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'DEVICE_MISMATCH',
                'message' => 'This license is bound to another device. Contact support to transfer machines.',
            ], Response::HTTP_FORBIDDEN);
        } else {
            // Existing matched device: update heartbeats
            $license->update(['last_validated_at' => Carbon::now()]);

            RegisteredDevice::updateOrCreate(
                [
                    'pharmacy_license_id' => $license->id,
                    'device_uuid' => $deviceUuid,
                ],
                [
                    'pharmacy_id' => $license->pharmacy_id,
                    'device_name' => $deviceName ?? $license->bound_device_name,
                    'platform' => $platform ?? $license->bound_platform?->value,
                    'app_version' => $appVersion,
                    'ip_address' => $request->ip(),
                    'last_seen_at' => Carbon::now(),
                    'is_active' => true,
                ]
            );
        }

        $pharmacy = $license->pharmacy;

        // Generate scoped session token for subsequent API actions (e.g. cloud backups)
        $sessionToken = $pharmacy->createToken(
            'device:' . $deviceUuid,
            ['backup:upload', 'license:read']
        )->plainTextToken;

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'pharmacy_name' => $pharmacy->pharmacy_name,
                'license_status' => $license->status->value,
                'session_days_remaining' => $license->days_remaining,
                'valid_until' => $license->valid_until->toIso8601String(),
                'bound_device_uuid' => $license->bound_device_uuid,
                'session_token' => $sessionToken,
            ],
        ], Response::HTTP_OK);
    }
}
