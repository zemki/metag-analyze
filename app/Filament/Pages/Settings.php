<?php

namespace App\Filament\Pages;

use App\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
            'api_v2_cutoff_date' => Setting::get('api_v2_cutoff_date', '2025-12-21'),
            'api_max_login_attempts' => Setting::get('api_max_login_attempts', 10),
            'api_lockout_duration' => Setting::get('api_lockout_duration', 30),
            'mart_max_login_attempts' => Setting::get('mart_max_login_attempts', 10),
            'mart_lockout_duration' => Setting::get('mart_lockout_duration', 30),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Checkbox::make('mart_enabled')
                            ->label('Enable MART Projects')
                            ->helperText('Allow users to create MART (Mobile Assessment Research Tool) projects'),

                        Placeholder::make('mart_warning')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="rounded-lg border border-warning-400 bg-warning-50 dark:bg-warning-950 dark:border-warning-700 p-4 text-sm">
                                    <p class="font-semibold text-warning-700 dark:text-warning-400">⚠️ Database Required</p>
                                    <p class="mt-1 text-warning-600 dark:text-warning-500">MART requires a separate database. Before enabling:</p>
                                    <ol class="list-decimal ml-5 mt-2 text-warning-600 dark:text-warning-500 space-y-1">
                                        <li>Create a new MySQL database for MART</li>
                                        <li>Configure DB_MART_* variables in .env</li>
                                        <li>Run: php artisan migrate</li>
                                    </ol>
                                </div>
                            ')),
                    ]),

                TextInput::make('max_studies_per_user')
                    ->label('Max Studies Per User')
                    ->helperText('Maximum number of projects a user can create')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(1000)
                    ->required(),

                DatePicker::make('api_v2_cutoff_date')
                    ->label('API V2 Cutoff Date')
                    ->helperText('Projects created before this date use API v1 (media field), after use API v2 (entity field)')
                    ->required()
                    ->displayFormat('Y-m-d')
                    ->format('Y-m-d'),

                TextInput::make('api_max_login_attempts')
                    ->label('API Max Login Attempts')
                    ->helperText('Maximum failed login attempts before lockout (applies to web and standard API login)')
                    ->numeric()
                    ->minValue(3)
                    ->maxValue(20)
                    ->required(),

                TextInput::make('api_lockout_duration')
                    ->label('API Lockout Duration (minutes)')
                    ->helperText('How long users are locked out after exceeding max API login attempts')
                    ->numeric()
                    ->minValue(5)
                    ->maxValue(120)
                    ->required(),

                TextInput::make('mart_max_login_attempts')
                    ->label('MART Max Login Attempts')
                    ->helperText('Maximum failed login attempts before lockout for MART mobile app login')
                    ->numeric()
                    ->minValue(3)
                    ->maxValue(20)
                    ->required(),

                TextInput::make('mart_lockout_duration')
                    ->label('MART Lockout Duration (minutes)')
                    ->helperText('How long MART users are locked out after exceeding max login attempts')
                    ->numeric()
                    ->minValue(5)
                    ->maxValue(120)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('mart_enabled', $data['mart_enabled'] ? '1' : '0', auth()->id());
        Setting::set('max_studies_per_user', $data['max_studies_per_user'], auth()->id());
        Setting::set('api_v2_cutoff_date', $data['api_v2_cutoff_date'], auth()->id(), 'date');
        Setting::set('api_max_login_attempts', $data['api_max_login_attempts'], auth()->id(), 'integer');
        Setting::set('api_lockout_duration', $data['api_lockout_duration'], auth()->id(), 'integer');
        Setting::set('mart_max_login_attempts', $data['mart_max_login_attempts'], auth()->id(), 'integer');
        Setting::set('mart_lockout_duration', $data['mart_lockout_duration'], auth()->id(), 'integer');

        // Clear dashboard stats cache so changes reflect immediately
        \Illuminate\Support\Facades\Cache::forget('dashboard_stats');

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->body('Your settings have been saved successfully.')
            ->send();
    }
}
