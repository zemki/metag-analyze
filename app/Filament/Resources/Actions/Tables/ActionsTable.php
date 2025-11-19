<?php

namespace App\Filament\Resources\Actions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->default('System'),

                TextColumn::make('name')
                    ->label('Action')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->searchable()
                    ->url(fn ($record) => $record->url, shouldOpenInNewTab: true)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('time')
                    ->label('Time')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable()
                    ->default(fn ($record) => $record->created_at),

                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                // View only - no edit/delete for audit log
            ])
            ->bulkActions([
                // No bulk actions for audit log
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}
