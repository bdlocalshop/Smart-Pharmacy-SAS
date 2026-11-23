<?php

namespace App\Filament\Resources\PharmacyBackups\Pages;

use App\Filament\Resources\PharmacyBackups\PharmacyBackupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPharmacyBackup extends ViewRecord
{
    protected static string $resource = PharmacyBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
