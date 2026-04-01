<?php

namespace App\Filament\Resources\DocumentFormats;

use App\Filament\Resources\DocumentFormats\Pages\CreateDocumentFormat;
use App\Filament\Resources\DocumentFormats\Pages\EditDocumentFormat;
use App\Filament\Resources\DocumentFormats\Pages\ListDocumentFormats;
use App\Filament\Resources\DocumentFormats\Schemas\DocumentFormatForm;
use App\Filament\Resources\DocumentFormats\Tables\DocumentFormatsTable;
use App\Models\DocumentFormat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentFormatResource extends Resource
{
    protected static ?string $model = DocumentFormat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocument;

    protected static ?string $navigationLabel = 'Document Formats';

    public static function form(Schema $schema): Schema
    {
        return DocumentFormatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentFormatsTable::configure($table);
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
            'index' => ListDocumentFormats::route('/'),
            'create' => CreateDocumentFormat::route('/create'),
            'edit' => EditDocumentFormat::route('/{record}/edit'),
        ];
    }
}
