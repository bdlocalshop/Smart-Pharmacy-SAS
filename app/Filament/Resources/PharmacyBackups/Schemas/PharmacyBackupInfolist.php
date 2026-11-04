<?php

declare(strict_types=1);

namespace App\Filament\Resources\PharmacyBackups\Schemas;

use App\Models\PharmacyBackup;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PharmacyBackupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Database Archive Metadata')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('pharmacy.pharmacy_name')
                                ->label('Originating Pharmacy')
                                ->weight('bold'),
                            TextEntry::make('device_uuid')
                                ->label('Originating Hardware Device UUID')
                                ->copyable(),
                            TextEntry::make('formatted_file_size')
                                ->label('Snapshot File Size')
                                ->badge()
                                ->color('info'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('file_path')
                                ->label('Vault Storage Location')
                                ->copyable(),
                            TextEntry::make('created_at')
                                ->label('Uploaded Timestamp')
                                ->dateTime(),
                        ]),
                        TextEntry::make('notes')
                            ->label('Client Backup Notes')
                            ->placeholder('No notes attached by client.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
