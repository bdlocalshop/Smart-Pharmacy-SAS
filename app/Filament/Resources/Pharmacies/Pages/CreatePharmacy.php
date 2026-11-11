<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pharmacies\Pages;

use App\Enums\VerificationStatus;
use App\Filament\Resources\Pharmacies\PharmacyResource;
use App\Mail\PharmacyLicenseCredentialsMail;
use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreatePharmacy extends CreateRecord
{
    protected static string $resource = PharmacyResource::class;

    protected function afterCreate(): void
    {
        /** @var Pharmacy $record */
        $record = $this->record;

        // If the pharmacy was created directly with APPROVED status, issue license and email credentials
        if ($record->verification_status === VerificationStatus::APPROVED && ! $record->licenses()->exists()) {
            $sessionDays = 30;
            $licenseKey = 'PHARMA-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
            $clientSecret = Str::random(32);

            $license = PharmacyLicense::create([
                'pharmacy_id' => $record->id,
                'license_key' => $licenseKey,
                'client_secret_hash' => Crypt::encryptString($clientSecret),
                'session_days_limit' => $sessionDays,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addDays($sessionDays),
                'is_revoked' => false,
            ]);

            $emailStatus = '';
            if (! empty($record->owner_email)) {
                try {
                    Mail::to($record->owner_email)->send(
                        new PharmacyLicenseCredentialsMail($record, $license, $clientSecret, false)
                    );
                    $emailStatus = "\nCredentials emailed to {$record->owner_email}.";
                } catch (\Throwable $e) {
                    Log::warning("Failed to send license email to {$record->owner_email}: " . $e->getMessage());
                    $emailStatus = "\nCould not send email: " . $e->getMessage();
                }
            }

            Notification::make()
                ->title('License Issued')
                ->body("License Key: {$licenseKey}\nHMAC Secret: {$clientSecret}{$emailStatus}")
                ->success()
                ->persistent()
                ->send();
        }
    }
}
