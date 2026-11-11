<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pharmacies\Schemas;

use App\Models\Pharmacy;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PharmacyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pharmacy Information')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('pharmacy_name')->weight('bold'),
                            TextEntry::make('trade_license_number'),
                            TextEntry::make('verification_status')
                                ->badge()
                                ->color(fn ($state) => $state?->color() ?? 'gray'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('pharmacy_phone')->icon('heroicon-m-phone'),
                            TextEntry::make('map_location_url')
                                ->label('Map Location Pin')
                                ->placeholder('Not provided')
                                ->icon('heroicon-m-map-pin')
                                ->url(fn (Pharmacy $record) => $record->map_location_url, shouldOpenInNewTab: true),
                        ]),
                        TextEntry::make('pharmacy_address')
                            ->label('Physical Address')
                            ->columnSpanFull(),
                    ]),

                Section::make('Owner Credentials')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('owner_name'),
                            TextEntry::make('owner_email')->placeholder('Not provided')->icon('heroicon-m-envelope'),
                            TextEntry::make('owner_phone')->icon('heroicon-m-phone'),
                        ]),
                        TextEntry::make('owner_address')->placeholder('Not provided')->columnSpanFull(),
                    ]),

                Section::make('Active License & Bound Hardware')
                    ->description('Cryptographic licensing state and single-device hardware lock.')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('activeLicense.license_key')
                                ->label('Active License Key')
                                ->placeholder('No license issued')
                                ->copyable()
                                ->weight('bold'),
                            TextEntry::make('activeLicense_secret')
                                ->label('HMAC Client Secret')
                                ->state(fn (Pharmacy $record) => $record->activeLicense?->getPlainSecret() ?: 'No license issued')
                                ->copyable()
                                ->placeholder('No license issued'),
                            TextEntry::make('activeLicense.status')
                                ->label('License State')
                                ->badge()
                                ->placeholder('N/A')
                                ->color(fn ($record) => $record->activeLicense?->status?->color() ?? 'gray'),
                            TextEntry::make('activeLicense.days_remaining')
                                ->label('Session Days Remaining')
                                ->suffix(' days')
                                ->placeholder('0'),
                        ]),
                        Grid::make(4)->schema([
                            TextEntry::make('activeLicense.valid_until')
                                ->label('Valid Until')
                                ->dateTime()
                                ->placeholder('N/A'),
                            TextEntry::make('activeLicense.bound_device_uuid')
                                ->label('Bound Hardware UUID')
                                ->placeholder('Unbound (Waiting for first device activation)')
                                ->copyable(),
                            TextEntry::make('activeLicense.bound_device_name')
                                ->label('Device Terminal Name')
                                ->placeholder('N/A'),
                            TextEntry::make('activeLicense.bound_platform')
                                ->label('Platform')
                                ->placeholder('N/A'),
                        ]),
                    ]),
            ]);
    }
}
