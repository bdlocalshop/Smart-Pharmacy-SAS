<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pharmacies\Schemas;

use App\Enums\VerificationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PharmacyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pharmacy Business Information')
                    ->description('Public identity and licensing credentials.')
                    ->schema([
                        TextInput::make('pharmacy_name')
                            ->label('Pharmacy Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('trade_license_number')
                            ->label('Trade License Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),
                        TextInput::make('pharmacy_phone')
                            ->label('Pharmacy Telephone')
                            ->tel()
                            ->required()
                            ->maxLength(50),
                        TextInput::make('opening_hours')
                            ->label('Opening Hours')
                            ->placeholder('e.g. 8:00AM-1:00PM, 2:00PM-5:00PM, 6:00PM-11:30PM')
                            ->helperText('Enter time slots separated by commas.')
                            ->maxLength(500)
                            ->nullable(),
                        TextInput::make('map_location_url')
                            ->label('Map Pin URL')
                            ->url()
                            ->nullable()
                            ->columnSpanFull(),
                        Textarea::make('pharmacy_address')
                            ->label('Full Physical Address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Ownership & Administration')
                    ->description('Owner personal details and onboarding status.')
                    ->schema([
                        TextInput::make('owner_name')
                            ->label('Owner Full Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('owner_email')
                            ->label('Owner Email Address')
                            ->email()
                            ->nullable()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('owner_phone')
                            ->label('Owner Direct Phone')
                            ->tel()
                            ->required()
                            ->maxLength(50),
                        Select::make('verification_status')
                            ->label('Verification Status')
                            ->options(VerificationStatus::class)
                            ->required()
                            ->default(VerificationStatus::PENDING),
                        Textarea::make('owner_address')
                            ->label('Owner Residential Address')
                            ->nullable()
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
