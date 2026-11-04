<?php

namespace App\Filament\Resources\PharmacyBackups;

use App\Filament\Resources\PharmacyBackups\Pages\CreatePharmacyBackup;
use App\Filament\Resources\PharmacyBackups\Pages\EditPharmacyBackup;
use App\Filament\Resources\PharmacyBackups\Pages\ListPharmacyBackups;
use App\Filament\Resources\PharmacyBackups\Pages\ViewPharmacyBackup;
use App\Filament\Resources\PharmacyBackups\Schemas\PharmacyBackupForm;
use App\Filament\Resources\PharmacyBackups\Schemas\PharmacyBackupInfolist;
use App\Filament\Resources\PharmacyBackups\Tables\PharmacyBackupsTable;
use App\Models\PharmacyBackup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PharmacyBackupResource extends Resource
{
    protected static ?string $model = PharmacyBackup::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Database Backups';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return PharmacyBackupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PharmacyBackupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PharmacyBackupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPharmacyBackups::route('/'),
            'create' => CreatePharmacyBackup::route('/create'),
            'view' => ViewPharmacyBackup::route('/{record}'),
            'edit' => EditPharmacyBackup::route('/{record}/edit'),
        ];
    }
}
