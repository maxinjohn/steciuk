<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Enums\PublishStatus;
use App\Filament\Support\SettingsFormTabs;
use App\Models\Page as SitePage;
use App\Models\Setting;
use App\Services\LaunchModeService;
use App\Services\MaintenanceModeService;
use App\Services\SecurityLogger;
use App\Support\SitePathGate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SiteMaintenanceSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SiteSettings;

    protected static ?string $navigationLabel = 'Maintenance mode';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Site Maintenance';

    protected static ?string $slug = 'site-maintenance';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasFullPanelAccess()
            || $user?->hasAdminPermission(AdminPermission::SettingsChurch);
    }

    public function mount(): void
    {
        $this->form->fill([
            'maintenance_gates' => MaintenanceModeService::gates(),
            'maintenance_mode_message' => Setting::get('maintenance_mode_message'),
            'maintenance_mode_title' => Setting::get('maintenance_mode_title'),
            'maintenance_mode_badge' => Setting::get('maintenance_mode_badge'),
            'maintenance_mode_chips' => json_decode(Setting::get('maintenance_mode_chips', '[]') ?: '[]', true) ?: [],
            'maintenance_mode_show_service_times' => Setting::get('maintenance_mode_show_service_times', '1') !== '0',
            'maintenance_mode_service_times_url' => Setting::get('maintenance_mode_service_times_url'),
            'maintenance_mode_service_times_label' => Setting::get('maintenance_mode_service_times_label', 'Service times'),
            'maintenance_mode_show_email' => Setting::get('maintenance_mode_show_email', '1') !== '0',
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $gates = collect($data['maintenance_gates'] ?? [])
            ->map(function (array $gate): array {
                if (blank($gate['id'] ?? null)) {
                    $gate['id'] = SitePathGate::newId('mg');
                }

                if (
                    ($gate['enabled'] ?? false)
                    && ($gate['scope'] ?? SitePathGate::SCOPE_SITE) === SitePathGate::SCOPE_PATH
                    && SitePathGate::normalizePath($gate['target_path'] ?? '') === ''
                ) {
                    throw ValidationException::withMessages([
                        'data.maintenance_gates' => 'Each enabled path maintenance rule needs a URL path.',
                    ]);
                }

                return MaintenanceModeService::normalizeGate($gate);
            })
            ->values()
            ->all();

        unset($data['maintenance_gates']);

        DB::transaction(function () use ($data, $gates): void {
            Setting::persistBatch(function () use ($data, $gates): void {
                MaintenanceModeService::saveGates($gates);

                foreach ($data as $key => $value) {
                    if ($key === 'maintenance_mode_chips') {
                        Setting::set($key, json_encode($value ?? []), 'general');

                        continue;
                    }

                    if (in_array($key, ['maintenance_mode_show_service_times', 'maintenance_mode_show_email'], true)) {
                        Setting::set($key, ($value ?? false) ? '1' : '0', 'general');

                        continue;
                    }

                    Setting::set($key, $value ?? '', 'general');
                }
            });
        });

        SecurityLogger::logSettingsSaved('Site maintenance settings');

        Notification::make()
            ->success()
            ->title('Maintenance settings saved')
            ->body(count(array_filter($gates, fn (array $gate): bool => (bool) ($gate['enabled'] ?? false))).' maintenance rule(s) saved.')
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $activeServices = MaintenanceModeService::activeServiceCount();

        return $schema
            ->components([
                SettingsFormTabs::make('Maintenance settings', [
                    Tab::make('Maintenance rules')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->schema([
                            Placeholder::make('maintenance_status')
                                ->label('Active rules')
                                ->content(fn (): string => MaintenanceModeService::adminStatusSummary())
                                ->columnSpanFull(),
                            Repeater::make('maintenance_gates')
                                ->label('Maintenance list')
                                ->helperText('Add rules for the entire site or specific URL paths. Each row has its own on/off toggle.')
                                ->schema([
                                    Toggle::make('enabled')
                                        ->label('Enabled')
                                        ->default(false)
                                        ->live()
                                        ->dehydrated(),
                                    TextInput::make('label')
                                        ->label('Admin rule name')
                                        ->placeholder('Site maintenance, Liturgy page work…')
                                        ->helperText('For your reference in admin only — not shown on the public maintenance page.')
                                        ->required()
                                        ->dehydrated(),
                                    Select::make('scope')
                                        ->label('Apply to')
                                        ->options([
                                            SitePathGate::SCOPE_SITE => 'Entire public site',
                                            SitePathGate::SCOPE_PATH => 'Specific URL path only',
                                        ])
                                        ->default(SitePathGate::SCOPE_SITE)
                                        ->live()
                                        ->dehydrated(),
                                    Select::make('theme')
                                        ->label('Page style')
                                        ->options(MaintenanceModeService::themeOptions())
                                        ->default(LaunchModeService::THEME_PARISH)
                                        ->helperText('Visual style for the public maintenance page. Rule names are admin-only and never shown to visitors.')
                                        ->dehydrated(),
                                    Section::make('URL path')
                                        ->visible(fn ($get): bool => ($get('scope') ?? SitePathGate::SCOPE_SITE) === SitePathGate::SCOPE_PATH)
                                        ->schema([
                                            Select::make('page_picker')
                                                ->label('Quick pick published page')
                                                ->options(fn (): array => SitePage::query()
                                                    ->where('status', PublishStatus::Published)
                                                    ->orderBy('title')
                                                    ->pluck('title', 'slug')
                                                    ->all())
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set): void {
                                                    if (filled($state)) {
                                                        $set('target_path', $state);
                                                    }
                                                })
                                                ->dehydrated(false),
                                            ViewField::make('pages_list')
                                                ->hiddenLabel()
                                                ->view('filament.admin.launch-pages-list')
                                                ->viewData(fn ($get): array => [
                                                    'pages' => SitePage::query()
                                                        ->where('status', PublishStatus::Published)
                                                        ->orderBy('title')
                                                        ->get(['title', 'slug'])
                                                        ->map(fn ($page): array => [
                                                            'title' => $page->title,
                                                            'slug' => $page->slug,
                                                            'url' => url('/'.$page->slug),
                                                        ])
                                                        ->all(),
                                                    'selectedPath' => SitePathGate::normalizePath($get('target_path')),
                                                ])
                                                ->dehydrated(false)
                                                ->columnSpanFull(),
                                            TextInput::make('target_path')
                                                ->label('URL path')
                                                ->placeholder('liturgy, events')
                                                ->dehydrated(),
                                            Select::make('path_match')
                                                ->label('Path matching')
                                                ->options([
                                                    SitePathGate::MATCH_PREFIX => 'This path and sub-pages',
                                                    SitePathGate::MATCH_EXACT => 'Exact path only',
                                                ])
                                                ->default(SitePathGate::MATCH_PREFIX)
                                                ->dehydrated(),
                                        ]),
                                    Toggle::make('use_global_copy')
                                        ->label('Use shared page copy (Page copy tab)')
                                        ->default(true)
                                        ->live()
                                        ->dehydrated(),
                                    TextInput::make('title')
                                        ->label('Custom headline')
                                        ->visible(fn ($get): bool => ! ($get('use_global_copy') ?? true))
                                        ->dehydrated(),
                                    TextInput::make('badge')
                                        ->label('Custom badge')
                                        ->visible(fn ($get): bool => ! ($get('use_global_copy') ?? true))
                                        ->dehydrated(),
                                    Textarea::make('message')
                                        ->label('Custom message')
                                        ->rows(3)
                                        ->visible(fn ($get): bool => ! ($get('use_global_copy') ?? true))
                                        ->columnSpanFull()
                                        ->dehydrated(),
                                    TextInput::make('id')->hidden()->dehydrated(),
                                ])
                                ->itemLabel(fn (array $state): ?string => SitePathGate::adminItemLabel($state, 'Maintenance'))
                                ->collapsible()
                                ->cloneable()
                                ->addActionLabel('Add maintenance rule')
                                ->defaultItems(0)
                                ->columnSpanFull(),
                        ]),
                    Tab::make('Page copy')
                        ->icon('heroicon-o-sparkles')
                        ->schema([
                            Section::make('Shared visitor text')
                                ->description('Used by maintenance rules that keep “Use shared page copy” turned on.')
                                ->schema([
                                    TextInput::make('maintenance_mode_title')
                                        ->label('Headline')
                                        ->placeholder('We\'ll be right back'),
                                    TextInput::make('maintenance_mode_badge')
                                        ->label('Status badge')
                                        ->placeholder('Under maintenance'),
                                    Repeater::make('maintenance_mode_chips')
                                        ->label('Highlight chips')
                                        ->schema([
                                            TextInput::make('label')
                                                ->label('Chip text')
                                                ->required(),
                                        ])
                                        ->columnSpanFull(),
                                    Textarea::make('maintenance_mode_message')
                                        ->label('Message')
                                        ->rows(4)
                                        ->columnSpanFull(),
                                    Placeholder::make('maintenance_comfort_verses')
                                        ->label('Scripture quote')
                                        ->content('Maintenance pages show a random comfort verse from **Site settings → Faith & comfort → Comfort verses**. Add or edit verses there.')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make('Actions')
                        ->icon('heroicon-o-link')
                        ->schema([
                            Section::make('Visitor actions')
                                ->schema([
                                    Toggle::make('maintenance_mode_show_service_times')
                                        ->label('Show service times button')
                                        ->helperText($activeServices > 0
                                            ? "{$activeServices} active worship ".str('service')->plural($activeServices).' found.'
                                            : 'No active worship services found.')
                                        ->live(),
                                    TextInput::make('maintenance_mode_service_times_label')
                                        ->label('Service times button label')
                                        ->default('Service times')
                                        ->visible(fn ($get): bool => (bool) $get('maintenance_mode_show_service_times')),
                                    TextInput::make('maintenance_mode_service_times_url')
                                        ->label('Custom service times link (optional)')
                                        ->placeholder('/service-times or https://…')
                                        ->visible(fn ($get): bool => (bool) $get('maintenance_mode_show_service_times')),
                                    Toggle::make('maintenance_mode_show_email')
                                        ->label('Show email the parish button')
                                        ->live(),
                                ]),
                        ]),
                ], 'maintenance-tab'),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save settings')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }
}
