<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Enums\PublishStatus;
use App\Filament\Support\SettingsFormTabs;
use App\Models\Page as SitePage;
use App\Models\Setting;
use App\Services\LaunchModeService;
use App\Services\SecurityLogger;
use App\Support\GatePageCopy;
use App\Support\SitePathGate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
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

class SiteLaunchSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRocketLaunch;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SiteSettings;

    protected static ?string $navigationLabel = 'Launch countdown';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Site Launch & Countdown';

    protected static ?string $slug = 'site-launch';

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
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->form->fill([
            'launch_gates' => LaunchModeService::gates(),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $gates = collect($data['launch_gates'] ?? [])
            ->map(function (array $gate): array {
                if (blank($gate['id'] ?? null)) {
                    $gate['id'] = SitePathGate::newId('lg');
                }

                $countdown = LaunchModeService::normalizeCountdownInput($gate['countdown_at'] ?? null);
                $launchStyle = LaunchModeService::normalizeLaunchStyle($gate['launch_style'] ?? LaunchModeService::STYLE_COUNTDOWN);

                if (
                    ($gate['enabled'] ?? false)
                    && $launchStyle === LaunchModeService::STYLE_COUNTDOWN
                    && $countdown === null
                ) {
                    throw ValidationException::withMessages([
                        'data.launch_gates' => 'Countdown launches need an end date and time.',
                    ]);
                }

                if (
                    ($gate['enabled'] ?? false)
                    && $launchStyle === LaunchModeService::STYLE_RIBBON
                    && ! (bool) ($gate['allow_admin_ribbon'] ?? true)
                ) {
                    throw ValidationException::withMessages([
                        'data.launch_gates' => 'Ribbon launches need admin ribbon cut enabled.',
                    ]);
                }

                if (
                    ($gate['enabled'] ?? false)
                    && ($gate['scope'] ?? LaunchModeService::SCOPE_SITE) === LaunchModeService::SCOPE_PATH
                    && SitePathGate::normalizePath($gate['target_path'] ?? '') === ''
                ) {
                    throw ValidationException::withMessages([
                        'data.launch_gates' => 'Each enabled path countdown needs a URL path or published page selected.',
                    ]);
                }

                $gate['countdown_at'] = $launchStyle === LaunchModeService::STYLE_RIBBON
                    ? ''
                    : ($countdown?->toIso8601String() ?? '');
                $gate['launch_style'] = $launchStyle;
                $gate['theme'] = LaunchModeService::normalizeTheme($gate['theme'] ?? LaunchModeService::THEME_PARISH);

                return LaunchModeService::normalizeGate($gate);
            })
            ->values()
            ->all();

        DB::transaction(function () use ($gates): void {
            Setting::persistBatch(function () use ($gates): void {
                LaunchModeService::saveGates($gates);
            });
        });

        SecurityLogger::logSettingsSaved('Site launch settings');
        $this->fillForm();

        Notification::make()
            ->success()
            ->title('Launch settings saved')
            ->body(count(array_filter($gates, fn (array $gate): bool => (bool) ($gate['enabled'] ?? false))).' countdown(s) saved.')
            ->send();
    }

    public function cutRibbon(?string $gateId = null): void
    {
        abort_unless(static::canAccess(), 403);

        $gate = $gateId ? LaunchModeService::gateById($gateId) : LaunchModeService::primaryEnabledGate();

        if ($gate === null || ! ($gate['enabled'] ?? false) || LaunchModeService::gateIsLive($gate)) {
            Notification::make()->warning()->title('Launch is not active')->send();

            return;
        }

        LaunchModeService::markGateLaunched((string) $gate['id'], disable: true);
        SecurityLogger::info('launch.ribbon_cut', auth()->id(), ['source' => 'admin_settings', 'gate_id' => $gate['id']]);

        Notification::make()
            ->success()
            ->title('Ribbon cut — gate lifted')
            ->body(SitePathGate::summaryLabel($gate).' is now live for visitors.')
            ->send();

        $this->fillForm();
    }

    public function resetGate(?string $gateId = null): void
    {
        abort_unless(static::canAccess(), 403);

        if ($gateId) {
            LaunchModeService::resetGate($gateId);
        } else {
            LaunchModeService::resetLaunch();
        }

        Notification::make()
            ->success()
            ->title('Countdown reset')
            ->body('You can enable the gate again and save.')
            ->send();

        $this->fillForm();
    }

    /**
     * @return array<int, array{title: string, slug: string, url: string}>
     */
    protected function publishedPagesList(): array
    {
        return SitePage::query()
            ->where('status', PublishStatus::Published)
            ->orderBy('title')
            ->get(['title', 'slug'])
            ->map(fn (SitePage $page): array => [
                'title' => $page->title,
                'slug' => $page->slug,
                'url' => url('/'.$page->slug),
            ])
            ->all();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SettingsFormTabs::make('Launch settings', [
                    Tab::make('Countdowns')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            Placeholder::make('launch_status')
                                ->label('Active countdowns')
                                ->content(fn (): string => LaunchModeService::adminStatusSummary())
                                ->columnSpanFull(),
                            Repeater::make('launch_gates')
                                ->label('Countdown list')
                                ->helperText('Add as many countdowns as you need — site-wide or per URL. Each row has its own on/off toggle.')
                                ->schema([
                                    Toggle::make('enabled')
                                        ->label('Enabled')
                                        ->default(false)
                                        ->live()
                                        ->dehydrated(),
                                    TextInput::make('label')
                                        ->label('Admin rule name')
                                        ->placeholder('Site launch, Liturgy page launch')
                                        ->helperText('For your reference in admin only — not shown on the public launch page.')
                                        ->required()
                                        ->dehydrated(),
                                    Select::make('scope')
                                        ->label('Apply to')
                                        ->options([
                                            LaunchModeService::SCOPE_SITE => 'Entire public site',
                                            LaunchModeService::SCOPE_PATH => 'Specific URL path only',
                                        ])
                                        ->default(LaunchModeService::SCOPE_SITE)
                                        ->live()
                                        ->dehydrated(),
                                    Select::make('launch_style')
                                        ->label('Launch type')
                                        ->options([
                                            LaunchModeService::STYLE_COUNTDOWN => 'Countdown timer — page goes live when the timer ends',
                                            LaunchModeService::STYLE_RIBBON => 'Cut the ribbon — no timer, launch when the ribbon is cut',
                                        ])
                                        ->default(LaunchModeService::STYLE_COUNTDOWN)
                                        ->live()
                                        ->dehydrated(),
                                    Select::make('theme')
                                        ->label('Page style')
                                        ->options(LaunchModeService::themeOptions())
                                        ->default(LaunchModeService::THEME_PARISH)
                                        ->helperText('Visual style for the public launch page. Rule names are admin-only.')
                                        ->dehydrated(),
                                    DateTimePicker::make('countdown_at')
                                        ->label('Countdown ends at')
                                        ->seconds(false)
                                        ->native(false)
                                        ->visible(fn ($get): bool => ($get('launch_style') ?? LaunchModeService::STYLE_COUNTDOWN) === LaunchModeService::STYLE_COUNTDOWN)
                                        ->dehydrated(),
                                    Toggle::make('show_countdown')
                                        ->label('Show live timer')
                                        ->default(true)
                                        ->visible(fn ($get): bool => ($get('launch_style') ?? LaunchModeService::STYLE_COUNTDOWN) === LaunchModeService::STYLE_COUNTDOWN)
                                        ->dehydrated(),
                                    Toggle::make('allow_admin_ribbon')
                                        ->label('Allow ribbon cut from admin')
                                        ->helperText('Only signed-in parish admins with launch settings access can cut the ribbon on the gate page.')
                                        ->default(true)
                                        ->visible(fn ($get): bool => ($get('launch_style') ?? LaunchModeService::STYLE_COUNTDOWN) === LaunchModeService::STYLE_RIBBON)
                                        ->dehydrated(),
                                    Section::make('URL path')
                                        ->visible(fn ($get): bool => ($get('scope') ?? LaunchModeService::SCOPE_SITE) === LaunchModeService::SCOPE_PATH)
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
                                                    'pages' => $this->publishedPagesList(),
                                                    'selectedPath' => SitePathGate::normalizePath($get('target_path')),
                                                ])
                                                ->dehydrated(false)
                                                ->columnSpanFull(),
                                            TextInput::make('target_path')
                                                ->label('URL path')
                                                ->placeholder('liturgy, events, news/summer-fair')
                                                ->dehydrated(),
                                            Select::make('path_match')
                                                ->label('Path matching')
                                                ->options([
                                                    LaunchModeService::MATCH_PREFIX => 'This path and sub-pages',
                                                    LaunchModeService::MATCH_EXACT => 'Exact path only',
                                                ])
                                                ->default(LaunchModeService::MATCH_PREFIX)
                                                ->dehydrated(),
                                        ]),
                                    Section::make('Visitor page text')
                                        ->collapsed()
                                        ->schema([
                                            TextInput::make('event_name')->label('Event name (optional)')->dehydrated(),
                                            TextInput::make('subtitle')->label('Badge line')->default(GatePageCopy::LAUNCH_SUBTITLE_COUNTDOWN)->dehydrated(),
                                            TextInput::make('title')->label('Headline')->dehydrated(),
                                            Textarea::make('message')->label('Message')->rows(3)->columnSpanFull()->dehydrated(),
                                            TextInput::make('verse')
                                                ->label('Scripture line')
                                                ->helperText('Leave blank to show a random comfort verse from Site settings → Faith & comfort.')
                                                ->dehydrated(),
                                            TextInput::make('verse_ref')
                                                ->label('Scripture reference')
                                                ->dehydrated(),
                                        ]),
                                    TextInput::make('id')->hidden()->dehydrated(),
                                    TextInput::make('launched_at')->hidden()->dehydrated(),
                                ])
                                ->itemLabel(fn (array $state): ?string => SitePathGate::adminItemLabel($state, 'Launch rule'))
                                ->collapsible()
                                ->cloneable()
                                ->addActionLabel('Add countdown')
                                ->defaultItems(0)
                                ->columnSpanFull(),
                        ]),
                    Tab::make('Help')
                        ->icon('heroicon-o-question-mark-circle')
                        ->schema([
                            Section::make('How this works')
                                ->schema([
                                    Placeholder::make('help_copy')
                                        ->hiddenLabel()
                                        ->content("**Countdown timer** — visitors see a ticking clock. When it hits zero, the page goes live automatically.\n\n**Cut the ribbon** — no countdown. Visitors see the ribbon ceremony; only signed-in parish admins can cut the ribbon to go live.\n\nChoose a **page style** for each rule. Maintenance mode always wins if both are on."),
                                ]),
                        ]),
                ], 'launch-tab'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $active = LaunchModeService::enabledGates()[0] ?? null;

        return [
            Action::make('preview')
                ->label('Preview countdown')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => LaunchModeService::previewUrl($active))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $active !== null && ! LaunchModeService::gateIsLive($active)),
            Action::make('cutRibbon')
                ->label(fn (): string => LaunchModeService::gateLaunchStyle($active ?? []) === LaunchModeService::STYLE_RIBBON
                    ? 'Cut the ribbon'
                    : 'Launch now')
                ->icon('heroicon-o-scissors')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Launch now?')
                ->modalDescription(fn (): string => LaunchModeService::gateLaunchStyle($active ?? []) === LaunchModeService::STYLE_RIBBON
                    ? 'This lifts the ribbon gate immediately for visitors.'
                    : 'This ends the countdown gate early and shows the live page.')
                ->action(fn (): mixed => $this->cutRibbon($active['id'] ?? null))
                ->visible(fn (): bool => $active !== null && ! LaunchModeService::gateIsLive($active)),
        ];
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
