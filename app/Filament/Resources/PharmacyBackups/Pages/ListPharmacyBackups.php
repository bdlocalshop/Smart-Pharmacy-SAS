<?php

namespace App\Filament\Resources\PharmacyBackups\Pages;

use App\Filament\Resources\PharmacyBackups\PharmacyBackupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPharmacyBackups extends ListRecords
{
    protected static string $resource = PharmacyBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
