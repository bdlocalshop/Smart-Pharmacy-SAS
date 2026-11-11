<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pharmacies\Tables;

use App\Enums\VerificationStatus;
use App\Mail\PharmacyLicenseCredentialsMail;
use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PharmaciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pharmacy_name')
                    ->label('Pharmacy Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('trade_license_number')
                    ->label('Trade License')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('owner_name')
                    ->label('Owner')
                    ->searchable()
                    ->description(fn (Pharmacy $record) => $record->owner_phone),

                TextColumn::make('verification_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (VerificationStatus $state) => $state->color())
                    ->sortable(),

                TextColumn::make('activeLicense.days_remaining')
                    ->label('Session Days')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        (int) $state > 10 => 'success',
                        (int) $state > 0 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state}d" : 'No License'),

                TextColumn::make('activeLicense.bound_device_uuid')
                    ->label('Device Lock')
                    ->placeholder('Unbound')
                    ->limit(12)
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('map_location_url')
                    ->label('Map')
                    ->icon('heroicon-m-map-pin')
                    ->url(fn (Pharmacy $record) => $record->map_location_url, shouldOpenInNewTab: true)
                    ->formatStateUsing(fn () => 'View Pin')
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('verification_status')
                    ->label('Verification Status')
                    ->options(VerificationStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),

                // 1. Approve Action with Session Days & Automatic Email Modal
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Pharmacy $record) => $record->verification_status !== VerificationStatus::APPROVED)
                    ->form([
                        TextInput::make('session_days')
                            ->label('Initial Session Limit (in Days)')
                            ->numeric()
                            ->default(30)
                            ->required()
                            ->minValue(1)
                            ->helperText('The issued license will be active for this duration from approval time.'),
                        Toggle::make('send_email')
                            ->label('Send License Key & Secret to Owner Email')
                            ->default(true)
                            ->helperText(fn (Pharmacy $record) => ! empty($record->owner_email)
                                ? "Credentials will be automatically emailed to: {$record->owner_email}"
                                : 'Warning: No owner email registered on this pharmacy.')
                            ->disabled(fn (Pharmacy $record) => empty($record->owner_email)),
                    ])
                    ->action(function (Pharmacy $record, array $data): void {
                        $sessionDays = (int) $data['session_days'];
                        $shouldSendEmail = ! empty($data['send_email']) && ! empty($record->owner_email);

                        $record->update(['verification_status' => VerificationStatus::APPROVED]);

                        // Check if an existing license was pre-generated & bound during API registration
                        $existingLicense = $record->activeLicense ?? $record->licenses()->latest()->first();

                        if ($existingLicense) {
                            $existingLicense->update([
                                'session_days_limit' => $sessionDays,
                                'valid_from' => Carbon::now(),
                                'valid_until' => Carbon::now()->addDays($sessionDays),
                                'is_revoked' => false,
                            ]);

                            $plainSecret = $existingLicense->getPlainSecret();
                            $deviceInfo = $existingLicense->bound_device_uuid
                                ? "Hardware Lock: {$existingLicense->bound_device_uuid} (Pre-bound at device signup)"
                                : 'Hardware Lock: Unbound (Waiting for initial activation)';

                            $emailNotice = '';
                            if ($shouldSendEmail) {
                                try {
                                    Mail::to($record->owner_email)->send(
                                        new PharmacyLicenseCredentialsMail($record, $existingLicense, $plainSecret, false)
                                    );
                                    $emailNotice = "\nCredentials emailed to {$record->owner_email}.";
                                } catch (\Throwable $e) {
                                    Log::warning("Failed to email credentials to {$record->owner_email}: " . $e->getMessage());
                                    $emailNotice = "\nNotice: Email delivery failed ({$e->getMessage()}).";
                                }
                            }

                            Notification::make()
                                ->title('Pharmacy Approved & License Activated')
                                ->body("License Key: {$existingLicense->license_key}\nActive for {$sessionDays} days.\n{$deviceInfo}{$emailNotice}")
                                ->success()
                                ->persistent()
                                ->send();
                        } else {
                            // Generate cryptographic license key & HMAC secret for web registration
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

                            $emailNotice = '';
                            if ($shouldSendEmail) {
                                try {
                                    Mail::to($record->owner_email)->send(
                                        new PharmacyLicenseCredentialsMail($record, $license, $clientSecret, false)
                                    );
                                    $emailNotice = "\nCredentials emailed to {$record->owner_email}.";
                                } catch (\Throwable $e) {
                                    Log::warning("Failed to email credentials to {$record->owner_email}: " . $e->getMessage());
                                    $emailNotice = "\nNotice: Email delivery failed ({$e->getMessage()}).";
                                }
                            }

                            Notification::make()
                                ->title('Pharmacy Approved & License Issued')
                                ->body("License Key: {$licenseKey}\nHMAC Secret: {$clientSecret}\nSave this secret securely; it cannot be viewed again in plaintext.{$emailNotice}")
                                ->success()
                                ->persistent()
                                ->send();
                        }
                    }),

                // 2. Collect / View Decrypted Credentials Action
                Action::make('credentials')
                    ->label('Credentials')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (Pharmacy $record) => $record->licenses()->exists())
                    ->modalHeading(fn (Pharmacy $record) => "Licensing Credentials — {$record->pharmacy_name}")
                    ->modalDescription('Collect decrypted cryptographic credentials or re-send them to the pharmacy owner email.')
                    ->modalSubmitActionLabel('Dispatch Email to Owner')
                    ->form([
                        TextInput::make('license_key')
                            ->label('License Key')
                            ->default(fn (Pharmacy $record) => $record->activeLicense?->license_key ?? $record->licenses()->latest()->first()?->license_key)
                            ->disabled()
                            ->readOnly()
                            ->copyable(),
                        TextInput::make('client_secret')
                            ->label('HMAC Client Secret (Decrypted)')
                            ->default(fn (Pharmacy $record) => $record->activeLicense?->getPlainSecret() ?? $record->licenses()->latest()->first()?->getPlainSecret())
                            ->disabled()
                            ->readOnly()
                            ->password()
                            ->revealable()
                            ->copyable()
                            ->helperText('Decrypted from secure cryptographic vault. Click the eye icon to reveal or copy.'),
                        TextInput::make('valid_until')
                            ->label('Valid Until')
                            ->default(fn (Pharmacy $record) => ($record->activeLicense ?? $record->licenses()->latest()->first())?->valid_until?->format('M d, Y H:i:s T') ?? 'N/A')
                            ->disabled()
                            ->readOnly(),
                        TextInput::make('hardware_lock')
                            ->label('Hardware Lock')
                            ->default(function (Pharmacy $record): string {
                                $lic = $record->activeLicense ?? $record->licenses()->latest()->first();
                                return $lic?->bound_device_uuid
                                    ? "Locked to: " . ($lic->bound_device_name ?: 'Workstation') . " ({$lic->bound_device_uuid})"
                                    : 'Unbound (Ready for first machine)';
                            })
                            ->disabled()
                            ->readOnly(),
                        Toggle::make('resend_email')
                            ->label('Dispatch Credentials to Owner Email')
                            ->default(true)
                            ->helperText(fn (Pharmacy $record) => ! empty($record->owner_email)
                                ? "Will be emailed to: {$record->owner_email}"
                                : 'No owner email registered on this pharmacy.')
                            ->disabled(fn (Pharmacy $record) => empty($record->owner_email)),
                    ])
                    ->action(function (Pharmacy $record, array $data): void {
                        if (! empty($data['resend_email']) && ! empty($record->owner_email)) {
                            $license = $record->activeLicense ?? $record->licenses()->latest()->first();
                            if ($license) {
                                try {
                                    Mail::to($record->owner_email)->send(
                                        new PharmacyLicenseCredentialsMail($record, $license, $license->getPlainSecret(), false)
                                    );
                                    Notification::make()
                                        ->title('Credentials Dispatched')
                                        ->body("License key and secret were emailed to {$record->owner_email}.")
                                        ->success()
                                        ->send();
                                    return;
                                } catch (\Throwable $e) {
                                    Notification::make()
                                        ->title('Email Failed')
                                        ->body("Could not send email: {$e->getMessage()}")
                                        ->danger()
                                        ->send();
                                    return;
                                }
                            }
                        }

                        Notification::make()
                            ->title('Credentials Inspected')
                            ->body('Credentials inspected without re-dispatching email.')
                            ->info()
                            ->send();
                    }),

                // 3. Recreate / Regenerate New Key & Secret Later
                Action::make('recreate_credentials')
                    ->label('Recreate Key & Secret')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('danger')
                    ->visible(fn (Pharmacy $record) => $record->verification_status === VerificationStatus::APPROVED)
                    ->requiresConfirmation()
                    ->modalHeading('Recreate License Key & HMAC Secret')
                    ->modalDescription('⚠️ Warning: This will generate a brand new License Key and HMAC Client Secret. Any previously configured workstations will be disconnected until updated with the new credentials.')
                    ->form([
                        TextInput::make('session_days')
                            ->label('New Session Limit (in Days)')
                            ->numeric()
                            ->default(fn (Pharmacy $record) => ($record->activeLicense ?? $record->licenses()->latest()->first())?->session_days_limit ?? 30)
                            ->required()
                            ->minValue(1)
                            ->helperText('Duration in days for the newly generated license.'),
                        Toggle::make('reset_hardware_lock')
                            ->label('Reset Hardware Lock / Unbind Device')
                            ->default(true)
                            ->helperText('Detach the currently bound machine UUID so the pharmacy can immediately bind a new workstation.'),
                        Toggle::make('send_email')
                            ->label('Email New Credentials to Owner')
                            ->default(true)
                            ->helperText(fn (Pharmacy $record) => ! empty($record->owner_email)
                                ? "Will be emailed to: {$record->owner_email}"
                                : 'No owner email registered.')
                            ->disabled(fn (Pharmacy $record) => empty($record->owner_email)),
                    ])
                    ->action(function (Pharmacy $record, array $data): void {
                        $sessionDays = (int) $data['session_days'];
                        $resetLock = ! empty($data['reset_hardware_lock']);
                        $sendEmail = ! empty($data['send_email']) && ! empty($record->owner_email);

                        // Generate brand new cryptographic credentials
                        $newLicenseKey = 'PHARMA-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
                        $newClientSecret = Str::random(32);

                        $license = $record->activeLicense ?? $record->licenses()->latest()->first();

                        if ($license) {
                            $license->update([
                                'license_key' => $newLicenseKey,
                                'client_secret_hash' => Crypt::encryptString($newClientSecret),
                                'session_days_limit' => $sessionDays,
                                'valid_from' => Carbon::now(),
                                'valid_until' => Carbon::now()->addDays($sessionDays),
                                'bound_device_uuid' => $resetLock ? null : $license->bound_device_uuid,
                                'bound_device_name' => $resetLock ? null : $license->bound_device_name,
                                'bound_platform' => $resetLock ? null : $license->bound_platform,
                                'is_revoked' => false,
                            ]);
                        } else {
                            $license = PharmacyLicense::create([
                                'pharmacy_id' => $record->id,
                                'license_key' => $newLicenseKey,
                                'client_secret_hash' => Crypt::encryptString($newClientSecret),
                                'session_days_limit' => $sessionDays,
                                'valid_from' => Carbon::now(),
                                'valid_until' => Carbon::now()->addDays($sessionDays),
                                'is_revoked' => false,
                            ]);
                        }

                        if ($resetLock) {
                            $record->registeredDevices()->update(['is_active' => false]);
                        }

                        $emailNotice = '';
                        if ($sendEmail) {
                            try {
                                Mail::to($record->owner_email)->send(
                                    new PharmacyLicenseCredentialsMail($record, $license, $newClientSecret, true)
                                );
                                $emailNotice = "\nNew credentials were sent to {$record->owner_email}.";
                            } catch (\Throwable $e) {
                                Log::warning("Failed to email regenerated credentials to {$record->owner_email}: " . $e->getMessage());
                                $emailNotice = "\nNotice: Email dispatch failed ({$e->getMessage()}).";
                            }
                        }

                        $lockNotice = $resetLock ? "\nHardware Lock: Reset (Unbound)" : '';

                        Notification::make()
                            ->title('License Credentials Regenerated')
                            ->body("New License Key: {$newLicenseKey}\nNew HMAC Secret: {$newClientSecret}{$lockNotice}{$emailNotice}")
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                // 2. Renew / Extend Session Action
                Action::make('renew')
                    ->label('Renew')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (Pharmacy $record) => $record->verification_status === VerificationStatus::APPROVED)
                    ->form([
                        TextInput::make('extend_days')
                            ->label('Additional Session Days to Add')
                            ->numeric()
                            ->default(30)
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function (Pharmacy $record, array $data): void {
                        $license = $record->activeLicense ?? $record->licenses()->latest()->first();

                        if (! $license) {
                            Notification::make()
                                ->title('No License Found')
                                ->body('Please approve the pharmacy first to issue an initial license.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $extendDays = (int) $data['extend_days'];
                        $baseDate = ($license->valid_until && $license->valid_until->isFuture())
                            ? $license->valid_until
                            : Carbon::now();

                        $license->update([
                            'valid_until' => $baseDate->copy()->addDays($extendDays),
                            'is_revoked' => false,
                        ]);

                        Notification::make()
                            ->title('Session Extended')
                            ->body("Added {$extendDays} days. Valid until {$license->valid_until->format('M d, Y')}.")
                            ->success()
                            ->send();
                    }),

                // 3. Suspend / Freeze Action
                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Suspend Pharmacy')
                    ->modalDescription('Suspending this pharmacy will immediately revoke its client API validation privileges until restored.')
                    ->visible(fn (Pharmacy $record) => $record->verification_status === VerificationStatus::APPROVED)
                    ->action(function (Pharmacy $record): void {
                        $record->update(['verification_status' => VerificationStatus::SUSPENDED]);

                        Notification::make()
                            ->title('Pharmacy Suspended')
                            ->body("{$record->pharmacy_name} has been suspended.")
                            ->warning()
                            ->send();
                    }),

                // 4. Reject Action
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Pharmacy Application')
                    ->modalDescription('Are you sure you want to reject this registration application?')
                    ->visible(fn (Pharmacy $record) => $record->verification_status === VerificationStatus::PENDING)
                    ->action(function (Pharmacy $record): void {
                        $record->update(['verification_status' => VerificationStatus::REJECTED]);

                        Notification::make()
                            ->title('Application Rejected')
                            ->body("{$record->pharmacy_name} application was rejected.")
                            ->danger()
                            ->send();
                    }),

                // 5. Reset Bound Device Action
                Action::make('reset_device')
                    ->label('Reset Device')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Hardware Device Binding')
                    ->modalDescription('This will detach the currently locked hardware device UUID. The pharmacy will be permitted to activate exactly one replacement device on its next API validation.')
                    ->visible(fn (Pharmacy $record) => ! empty($record->activeLicense?->bound_device_uuid))
                    ->action(function (Pharmacy $record): void {
                        $license = $record->activeLicense;
                        if ($license) {
                            $license->update([
                                'bound_device_uuid' => null,
                                'bound_device_name' => null,
                                'bound_platform' => null,
                            ]);

                            $record->registeredDevices()->update(['is_active' => false]);

                            Notification::make()
                                ->title('Device Lock Cleared')
                                ->body('The device lock was successfully cleared. The pharmacy may now bind a new machine.')
                                ->success()
                                ->send();
                        }
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
