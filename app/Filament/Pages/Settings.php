<?php

namespace App\Filament\Pages;

use App\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationLabel(): string
    {
        return 'Settings';
    }

    public function getTitle(): string
    {
        return 'Settings';
    }

    public function mount(): void
    {
        $this->form->fill([
            'mart_enabled' => Setting::get('mart_enabled', true),
            'max_studies_per_user' => Setting::get('max_studies_per_user', 100),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Checkbox::make('mart_enabled')
                    ->label('Enable MART Projects')
                    ->helperText('Allow users to create MART (Mobile Assessment Research Tool) projects'),

                TextInput::make('max_studies_per_user')
                    ->label('Max Studies Per User')
                    ->helperText('Maximum number of projects a user can create')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(1000)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('mart_enabled', $data['mart_enabled'] ? '1' : '0', auth()->id());
        Setting::set('max_studies_per_user', $data['max_studies_per_user'], auth()->id());

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->body('Your settings have been saved successfully.')
            ->send();
    }
}
