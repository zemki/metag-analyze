<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntryResource\Pages;
use App\Filament\Resources\EntryResource\RelationManagers;
use App\Entry;
use App\Media;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Panel;

class EntryResource extends Resource
{
    protected static ?string $model = Entry::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Tables\Columns\TextColumn::make('id')->grow(false)->sortable(),
                    Stack::make([
                    
            Tables\Columns\TextColumn::make('begin')
                ->formatStateUsing(function ($state) {
                    $date = new \DateTime($state);
                    return $date->format('d.m.Y H:i:s');
                }),
            Tables\Columns\TextColumn::make('end')
                ->formatStateUsing(function ($state) {
                    $date = new \DateTime($state);
                    return $date->format('d.m.Y H:i:s');
                }),
                ])->grow(false),
                Panel::make([
            Tables\Columns\TextColumn::make('inputs')
                ->formatStateUsing(function (string $state): string {
                    $json = json_decode($state, true);
                    $lines = [];
                    // Parse "firstValue" field if it exists
                    if (isset($json['firstValue'])) {
                        $firstValue = $json['firstValue'];


                        foreach ($json as $key => $value) {
                            if ($key !== 'firstValue') {
                                $lines[] = "{$key}: {$value}";
                            }
                        }

                        // Retrieve media name from Media model
                        $media = Media::find($firstValue['media_id']);
                        $media_name = $media ? $media->name : '';
                        $lines[] = "media-id : {$firstValue['media_id']} - media-name : {$media_name}";

                        foreach ($firstValue as $key => $value) {
                            // If the value is a JSON string, decode it and print its fields
                            if ($key === 'inputs' && is_string($value)) {
                                $inputs = json_decode($value, true);
                                foreach ($inputs as $inputKey => $inputValue) {
                                    $lines[] = "Input - {$inputKey}: {$inputValue}";
                                }
                            } elseif ($key !== 'media_id') {
                                // Ignore the already processed keys
                                $lines[] = "{$key}: {$value}";
                            }
                        }
                    }
                    return implode("\n", $lines);
                })])->collapsible()
                ]),
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
            'index' => Pages\ListEntries::route('/'),
            'create' => Pages\CreateEntry::route('/create'),
            
        ];
    }
}
