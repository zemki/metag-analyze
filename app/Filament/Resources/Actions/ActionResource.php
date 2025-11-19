<?php

namespace App\Filament\Resources\Actions;

use App\Action;
use App\Filament\Resources\Actions\Pages\ListActions;
use App\Filament\Resources\Actions\Schemas\ActionForm;
use App\Filament\Resources\Actions\Tables\ActionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActionResource extends Resource
{
    protected static ?string $model = Action::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Activity Log';

    protected static ?string $modelLabel = 'Action';

    protected static ?string $pluralModelLabel = 'Actions';

    public static function form(Schema $schema): Schema
    {
        return ActionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActionsTable::configure($table);
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
            'index' => ListActions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
