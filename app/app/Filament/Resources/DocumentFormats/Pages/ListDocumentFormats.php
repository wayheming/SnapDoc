<?php

namespace App\Filament\Resources\DocumentFormats\Pages;

use App\Filament\Resources\DocumentFormats\DocumentFormatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentFormats extends ListRecords
{
    protected static string $resource = DocumentFormatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
