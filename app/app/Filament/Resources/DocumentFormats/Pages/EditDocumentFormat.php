<?php

namespace App\Filament\Resources\DocumentFormats\Pages;

use App\Filament\Resources\DocumentFormats\DocumentFormatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentFormat extends EditRecord
{
    protected static string $resource = DocumentFormatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
