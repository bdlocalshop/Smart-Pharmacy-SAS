<?php

namespace App\Filament\Resources\PharmacyBackups\Pages;

use App\Filament\Resources\PharmacyBackups\PharmacyBackupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPharmacyBackup extends EditRecord
{
    protected static string $resource = PharmacyBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
