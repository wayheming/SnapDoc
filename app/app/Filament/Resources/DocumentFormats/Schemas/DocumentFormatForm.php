<?php

namespace App\Filament\Resources\DocumentFormats\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentFormatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                TextInput::make('country')
                    ->label('Country (ISO)')
                    ->maxLength(2)
                    ->required(),
                TextInput::make('width_mm')
                    ->label('Width, mm')
                    ->required()
                    ->numeric(),
                TextInput::make('height_mm')
                    ->label('Height, mm')
                    ->required()
                    ->numeric(),
                TextInput::make('dpi')
                    ->label('DPI')
                    ->required()
                    ->numeric()
                    ->default(300),
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
                Checkbox::make('is_active')
                    ->label('Active'),
            ]);
    }
}
