<?php

declare(strict_types=1);

use App\Http\Controllers\Api\BackupUploadController;
use App\Http\Controllers\Api\LicenseValidationController;
use App\Http\Controllers\Api\OwnerVerificationApiController;
use App\Http\Controllers\Api\PharmacyRegistrationApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // 1. Local Device App Self-Registration & Hardware Binding
    Route::post('/pharmacy/register', [PharmacyRegistrationApiController::class, 'register'])
        ->middleware(['throttle:30,1'])
        ->name('api.v1.pharmacy.register');

    Route::post('/register', [PharmacyRegistrationApiController::class, 'register'])
        ->middleware(['throttle:30,1'])
        ->name('api.v1.register');

    // 2. Local Device Registration & Activation Status Check
    Route::get('/pharmacy/status', [PharmacyRegistrationApiController::class, 'status'])
        ->middleware(['throttle:60,1'])
        ->name('api.v1.pharmacy.status');

    // 3. Strict single-device license validation (HMAC authenticated, rate limited)
    Route::post('/license/validate', [LicenseValidationController::class, 'validateLicense'])
        ->middleware(['throttle:60,1', 'hmac'])
        ->name('api.v1.license.validate');

    // 4. Encrypted database backup upload endpoint (Sanctum Bearer or HMAC authenticated)
    Route::post('/backup/upload', [BackupUploadController::class, 'upload'])
        ->middleware(['throttle:60,1', 'client.auth'])
        ->name('api.v1.backup.upload');

    // Backward-compatible alias as specified in PRD Section 3.2
    Route::post('/client/backup', [BackupUploadController::class, 'upload'])
        ->middleware(['throttle:60,1', 'client.auth'])
        ->name('api.v1.client.backup');

    // 5. Verify Owner Credentials from Unbound Machine (New Machine)
    Route::post('/pharmacy/verify-credentials', [OwnerVerificationApiController::class, 'verifyCredentials'])
        ->middleware(['throttle:30,1'])
        ->name('api.v1.pharmacy.verify-credentials');

    Route::post('/owner/verify-credentials', [OwnerVerificationApiController::class, 'verifyCredentials'])
        ->middleware(['throttle:30,1'])
        ->name('api.v1.owner.verify-credentials');
});

// Backward-compatible aliases for external clients omitting /v1
Route::post('/pharmacy/register', [PharmacyRegistrationApiController::class, 'register'])
    ->middleware(['throttle:30,1']);
Route::post('/register', [PharmacyRegistrationApiController::class, 'register'])
    ->middleware(['throttle:30,1']);
Route::get('/pharmacy/status', [PharmacyRegistrationApiController::class, 'status'])
    ->middleware(['throttle:60,1']);
Route::post('/pharmacy/verify-credentials', [OwnerVerificationApiController::class, 'verifyCredentials'])
    ->middleware(['throttle:30,1']);
Route::post('/owner/verify-credentials', [OwnerVerificationApiController::class, 'verifyCredentials'])
    ->middleware(['throttle:30,1']);
