<?php

namespace App\Filament\Resources\CasesResource\Pages;

use App\Filament\Resources\CasesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCases extends EditRecord
{
    protected static string $resource = CasesResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
