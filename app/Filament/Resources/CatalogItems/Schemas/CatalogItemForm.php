<?php

declare(strict_types=1);

namespace App\Filament\Resources\CatalogItems\Schemas;

use App\Enums\ItemType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CatalogItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->description('Public product or service specifications.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('item_type')
                            ->label('Item Classification')
                            ->options(ItemType::class)
                            ->required()
                            ->default(ItemType::PRODUCT),

                        TextInput::make('price')
                            ->label('Base Price (USD)')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('Published & Visible in Storefront')
                            ->default(true)
                            ->columnSpanFull(),

                        Textarea::make('short_description')
                            ->label('Short Summary')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        RichEditor::make('full_description')
                            ->label('Detailed Product Description & Technical Specs')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Media Upload')
                    ->description('High-resolution product cover image or banner.')
                    ->schema([
                        FileUpload::make('cover_image_path')
                            ->label('Cover Image')
                            ->image()
                            ->directory('catalog')
                            ->imageResizeMode('cover')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
