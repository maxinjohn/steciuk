<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Filament\Support\SettingsFormTabs;
use App\Models\Setting;
use App\Services\SecurityLogger;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteContentSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SiteSettings;

    protected static ?string $navigationLabel = 'Public site copy';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Public Site Copy';

    protected static ?string $slug = 'site-content-settings';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasFullPanelAccess()
            || $user?->hasAdminPermission(AdminPermission::SettingsChurch);
    }

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_announcement_enabled' => Setting::get('site_announcement_enabled', '0') === '1',
            'site_announcement_text' => Setting::get('site_announcement_text'),
            'site_announcement_link' => Setting::get('site_announcement_link'),
            'site_announcement_link_label' => Setting::get('site_announcement_link_label', 'Learn more'),
            'service_times_heading' => Setting::get('service_times_heading', 'Service Times'),
            'service_times_intro' => Setting::get('service_times_intro'),
            'events_list_heading' => Setting::get('events_list_heading', 'Upcoming Events'),
            'events_list_intro' => Setting::get('events_list_intro'),
            'sermons_list_heading' => Setting::get('sermons_list_heading', 'Sermons & Preaching'),
            'sermons_list_intro' => Setting::get('sermons_list_intro'),
            'prayer_request_heading' => Setting::get('prayer_request_heading', 'Prayer Request'),
            'prayer_request_intro' => Setting::get('prayer_request_intro'),
            'give_button_label' => Setting::get('give_button_label', 'Give'),
            'footer_copyright' => Setting::get('footer_copyright'),
            'footer_tagline' => Setting::get('footer_tagline'),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::persistBatch(function () use ($data): void {
            foreach ($data as $key => $value) {
                if ($key === 'site_announcement_enabled') {
                    Setting::set($key, ($value ?? false) ? '1' : '0', 'general');

                    continue;
                }

                Setting::set($key, $value ?? '', 'general');
            }
        });

        SecurityLogger::logSettingsSaved('Public site content settings');

        Notification::make()
            ->success()
            ->title('Public site copy saved')
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SettingsFormTabs::make('Public site copy', [
                    Tab::make('Announcement')
                        ->icon('heroicon-o-megaphone')
                        ->schema([
                            Section::make('Announcement ribbon')
                                ->description('Optional banner at the top of every public page — great for Holy Week, guest preachers, or parish news.')
                                ->schema([
                                    Toggle::make('site_announcement_enabled')
                                        ->label('Show announcement ribbon')
                                        ->live(),
                                    TextInput::make('site_announcement_text')
                                        ->label('Announcement text')
                                        ->maxLength(240)
                                        ->columnSpanFull(),
                                    TextInput::make('site_announcement_link')
                                        ->label('Link URL')
                                        ->url(),
                                    TextInput::make('site_announcement_link_label')
                                        ->label('Link label')
                                        ->default('Learn more'),
                                ]),
                        ]),
                    Tab::make('Worship')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Section::make('Worship & listings')
                                ->schema([
                                    TextInput::make('service_times_heading')
                                        ->default('Service Times'),
                                    Textarea::make('service_times_intro')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    TextInput::make('events_list_heading')
                                        ->default('Upcoming Events'),
                                    Textarea::make('events_list_intro')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                    TextInput::make('sermons_list_heading')
                                        ->default('Sermons & Preaching'),
                                    Textarea::make('sermons_list_intro')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make('Prayer & giving')
                        ->icon('heroicon-o-hand-raised')
                        ->schema([
                            Section::make('Prayer & giving')
                                ->schema([
                                    TextInput::make('prayer_request_heading')
                                        ->default('Prayer Request'),
                                    Textarea::make('prayer_request_intro')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    TextInput::make('give_button_label')
                                        ->default('Give')
                                        ->maxLength(40)
                                        ->helperText('Label for the header Give button — bank details are managed under Giving & donations → Giving page & bank details.'),
                                ]),
                        ]),
                    Tab::make('Footer')
                        ->icon('heroicon-o-bars-3-bottom-left')
                        ->schema([
                            Section::make('Footer extras')
                                ->schema([
                                    TextInput::make('footer_tagline')
                                        ->label('Footer tagline')
                                        ->columnSpanFull(),
                                    TextInput::make('footer_copyright')
                                        ->label('Copyright line')
                                        ->placeholder('© {year} St. Thomas Evangelical Church of India – UK Parish')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ], 'content-tab'),
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
                                ->label('Save public copy')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }
}
