<?php

namespace App\Filament\Resources\Actions\Pages;

use App\Filament\Resources\Actions\ActionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAction extends EditRecord
{
    protected static string $resource = ActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
