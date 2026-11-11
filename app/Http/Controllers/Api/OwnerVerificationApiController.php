<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class OwnerVerificationApiController extends Controller
{
    /**
     * Check owner credentials from an unbound device (new machine).
     * Returns full pharmacy and owner info, omitting cryptographic secrets.
     */
    public function verifyCredentials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['nullable', 'string', 'max:255'],
            'trade_license_number' => ['nullable', 'string', 'max:100'],
            'owner_email' => ['nullable', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'license_key' => ['nullable', 'string', 'max:64'],
            'password' => ['required', 'string'],
            'device_uuid' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Pharmacy::with(['activeLicense', 'licenses']);

        // 1. Resolve pharmacy record by whichever identifier was supplied
        if (! empty($validated['identifier'])) {
            $ident = trim($validated['identifier']);
            $query->where(function ($q) use ($ident) {
                $q->where('trade_license_number', $ident)
                    ->orWhere('owner_email', $ident)
                    ->orWhere('owner_phone', $ident)
                    ->orWhere('pharmacy_phone', $ident)
                    ->orWhereHas('licenses', function ($lq) use ($ident) {
                        $lq->where('license_key', $ident);
                    });
            });
        } elseif (! empty($validated['trade_license_number'])) {
            $query->where('trade_license_number', trim($validated['trade_license_number']));
        } elseif (! empty($validated['owner_email'])) {
            $query->where('owner_email', trim($validated['owner_email']));
        } elseif (! empty($validated['owner_phone'])) {
            $query->where('owner_phone', trim($validated['owner_phone']));
        } elseif (! empty($validated['license_key'])) {
            $query->whereHas('licenses', function ($lq) use ($validated) {
                $lq->where('license_key', trim($validated['license_key']));
            });
        } else {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'MISSING_IDENTIFIER',
                'message' => 'Please provide an identifier (trade_license_number, owner_email, owner_phone, or license_key) along with your password.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pharmacy = $query->first();

        // 2. Constant-time password verification
        if (! $pharmacy || ! Hash::check($validated['password'], $pharmacy->password)) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'INVALID_CREDENTIALS',
                'message' => 'The provided owner credentials do not match our records.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 3. Check pharmacy account status
        if ($pharmacy->verification_status === VerificationStatus::SUSPENDED) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'PHARMACY_SUSPENDED',
                'message' => 'This pharmacy account has been suspended by administration.',
            ], Response::HTTP_FORBIDDEN);
        }

        // 4. Resolve license and hardware lock status (WITHOUT exposing secrets)
        $activeLicense = $pharmacy->activeLicense ?? $pharmacy->licenses()->latest()->first();
        $callingDeviceUuid = ! empty($validated['device_uuid']) ? trim($validated['device_uuid']) : null;

        $isAccountDeviceLocked = false;
        $isCallingDeviceBound = false;
        $boundDeviceName = null;

        if ($activeLicense) {
            $isAccountDeviceLocked = ! empty($activeLicense->bound_device_uuid);
            $boundDeviceName = $activeLicense->bound_device_name;
            if ($callingDeviceUuid && $activeLicense->bound_device_uuid === $callingDeviceUuid) {
                $isCallingDeviceBound = true;
            }
        }

        // 5. Build clean, non-secret response payload
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Owner credentials verified successfully.',
            'data' => [
                'pharmacy' => [
                    'id' => $pharmacy->id,
                    'pharmacy_name' => $pharmacy->pharmacy_name,
                    'trade_license_number' => $pharmacy->trade_license_number,
                    'pharmacy_phone' => $pharmacy->pharmacy_phone,
                    'pharmacy_address' => $pharmacy->pharmacy_address,
                    'map_location_url' => $pharmacy->map_location_url,
                    'verification_status' => $pharmacy->verification_status->value,
                    'is_approved' => $pharmacy->verification_status === VerificationStatus::APPROVED,
                    'registered_at' => $pharmacy->created_at?->toIso8601String(),
                ],
                'owner' => [
                    'name' => $pharmacy->owner_name,
                    'email' => $pharmacy->owner_email,
                    'phone' => $pharmacy->owner_phone,
                    'address' => $pharmacy->owner_address,
                ],
                'license_summary' => [
                    'has_license' => $activeLicense !== null,
                    'license_key' => $activeLicense?->license_key,
                    'status' => $activeLicense?->status->value,
                    'session_days_remaining' => $activeLicense?->days_remaining,
                    'valid_until' => $activeLicense?->valid_until?->toIso8601String(),
                ],
                'hardware_lock' => [
                    'is_locked_to_device' => $isAccountDeviceLocked,
                    'bound_device_name' => $boundDeviceName,
                    'calling_device_uuid' => $callingDeviceUuid,
                    'is_calling_device_bound' => $isCallingDeviceBound,
                    'binding_required' => ! $isCallingDeviceBound,
                    'notice' => $isCallingDeviceBound
                        ? 'This machine is the authorized bound hardware.'
                        : ($isAccountDeviceLocked
                            ? 'This license is locked to another machine (' . ($boundDeviceName ?: 'Registered Workstation') . '). To activate this new machine, reset the device lock in Filament Admin or contact administrator.'
                            : 'No machine is currently locked. This license is ready for first-time hardware binding.'),
                ],
            ],
        ], Response::HTTP_OK);
    }
}
