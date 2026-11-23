<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PharmacyBackup;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BackupUploadController extends Controller
{
    /**
     * Upload and securely archive database snapshot backups.
     */
    public function upload(Request $request): JsonResponse
    {
        // Resolve authenticated pharmacy via either Sanctum or HMAC middleware
        /** @var Pharmacy|null $pharmacy */
        $pharmacy = $request->user();
        if (! $pharmacy instanceof Pharmacy) {
            $pharmacy = $request->attributes->get('pharmacy');
        }

        if (! $pharmacy) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'UNAUTHORIZED',
                'message' => 'Authentication required to upload backups.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'backup_file' => [
                'required',
                'file',
                'max:51200', // 50MB max
            ],
            'device_uuid' => ['required', 'string', 'max:255'],
            'backup_notes' => ['nullable', 'string', 'max:255'],
        ]);

        $deviceUuid = $validated['device_uuid'];
        $activeLicense = $pharmacy->activeLicense;

        if (! $activeLicense) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'NO_ACTIVE_LICENSE',
                'message' => 'No active, valid license found for this pharmacy.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Enforce device match against active license
        if ($activeLicense->bound_device_uuid && $activeLicense->bound_device_uuid !== $deviceUuid) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'DEVICE_MISMATCH',
                'message' => 'Backup rejected: The uploading device UUID does not match the registered hardware binding.',
            ], Response::HTTP_FORBIDDEN);
        }

        $file = $request->file('backup_file');
        $directory = "backups/{$pharmacy->id}";
        $timestamp = Carbon::now()->format('Ymd_His');
        $extension = $file->getClientOriginalExtension() ?: 'gz';
        $safeFilename = "{$timestamp}_{$deviceUuid}.{$extension}";

        // Securely store under storage/app/backups/{pharmacy_id}/ on private disk
        $disk = config('filesystems.disks.private') ? 'private' : 'local';
        $storedPath = $file->storeAs($directory, $safeFilename, [
            'disk' => $disk,
            'visibility' => 'private',
        ]);

        $backup = PharmacyBackup::create([
            'pharmacy_id' => $pharmacy->id,
            'device_uuid' => $deviceUuid,
            'file_path' => $storedPath,
            'file_size_bytes' => $file->getSize(),
            'notes' => $validated['backup_notes'] ?? null,
        ]);

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Database backup safely archived.',
            'backup_id' => $backup->id,
            'timestamp' => $backup->created_at->toIso8601String(),
        ], Response::HTTP_CREATED);
    }
}
