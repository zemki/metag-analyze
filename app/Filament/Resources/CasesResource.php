<?php

namespace App\Filament\Resources;

use App\Cases;
use App\Filament\Resources\CasesResource\Pages;
use App\User;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class CasesResource extends Resource
{
    protected static ?string $model = Cases::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('user_id')->grow(false)->formatStateUsing(function ($state) {
                    $user = User::find($state);
                    $user_name = $user ? $user->email : '';

                    return $user_name;
                })->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable(),
                Tables\Columns\TextColumn::make('duration')->sortable(),
                Tables\Columns\TextColumn::make('entries_count')->counts('entries')->sortable(),

                //
            ])
            ->filters([
                //
            ])
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListCases::route('/'),
            'create' => Pages\CreateCases::route('/create'),
        ];
    }
}
