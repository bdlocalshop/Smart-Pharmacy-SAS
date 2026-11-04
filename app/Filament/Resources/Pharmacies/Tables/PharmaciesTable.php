<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pharmacies\Tables;

use App\Enums\VerificationStatus;
use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;
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

                // 1. Approve Action with Session Days Modal
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
                    ])
                    ->action(function (Pharmacy $record, array $data): void {
                        $sessionDays = (int) $data['session_days'];

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

                            $deviceInfo = $existingLicense->bound_device_uuid
                                ? "Hardware Lock: {$existingLicense->bound_device_uuid} (Pre-bound at device signup)"
                                : 'Hardware Lock: Unbound (Waiting for initial activation)';

                            Notification::make()
                                ->title('Pharmacy Approved & License Activated')
                                ->body("License Key: {$existingLicense->license_key}\nActive for {$sessionDays} days.\n{$deviceInfo}")
                                ->success()
                                ->persistent()
                                ->send();
                        } else {
                            // Generate cryptographic license key & HMAC secret for web registration
                            $licenseKey = 'PHARMA-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
                            $clientSecret = Str::random(32);

                            PharmacyLicense::create([
                                'pharmacy_id' => $record->id,
                                'license_key' => $licenseKey,
                                'client_secret_hash' => Crypt::encryptString($clientSecret),
                                'session_days_limit' => $sessionDays,
                                'valid_from' => Carbon::now(),
                                'valid_until' => Carbon::now()->addDays($sessionDays),
                                'is_revoked' => false,
                            ]);

                            Notification::make()
                                ->title('Pharmacy Approved & License Issued')
                                ->body("License Key: {$licenseKey}\nHMAC Secret: {$clientSecret}\nSave this secret securely; it cannot be viewed again in plaintext.")
                                ->success()
                                ->persistent()
                                ->send();
                        }
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
