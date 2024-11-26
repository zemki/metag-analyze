<?php

namespace App\Filament\Resources\EntryResource\Pages;

use App\Filament\Resources\EntryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEntries extends ListRecords
{
    protected static string $resource = EntryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
