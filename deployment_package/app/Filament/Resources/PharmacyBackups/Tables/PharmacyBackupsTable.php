<?php

declare(strict_types=1);

namespace App\Filament\Resources\PharmacyBackups\Tables;

use App\Models\PharmacyBackup;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PharmacyBackupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pharmacy.pharmacy_name')
                    ->label('Pharmacy')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('device_uuid')
                    ->label('Origin Device UUID')
                    ->searchable()
                    ->copyable()
                    ->limit(18)
                    ->badge()
                    ->color('gray'),

                TextColumn::make('formatted_file_size')
                    ->label('Archive Size')
                    ->badge()
                    ->color('info')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('file_size_bytes', $direction);
                    }),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->placeholder('None'),

                TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),

                // Secure streamed download from private storage
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (PharmacyBackup $record) {
                        $disk = config('filesystems.disks.private') ? 'private' : 'local';

                        if (Storage::disk($disk)->exists($record->file_path)) {
                            return Storage::disk($disk)->download($record->file_path);
                        }

                        Notification::make()
                            ->title('Archive Not Found')
                            ->body('The requested file could not be found on secure private storage.')
                            ->danger()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
