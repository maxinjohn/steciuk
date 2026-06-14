<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Filament\Support\PublicExperienceFormSchema;
use App\Models\Setting;
use App\Services\SecurityLogger;
use App\Support\PublicUiCopyLibrary;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PublicExperienceSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SiteSettings;

    protected static ?string $navigationLabel = 'Public experience';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Public Experience';

    protected static ?string $slug = 'public-experience';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * @var list<string>
     */
    private const JSON_KEYS = [
        'public_ui_spark_strip',
        'public_ui_divine_whispers',
        'public_ui_action_strip',
        'public_ui_page_intro',
        'public_ui_prayer_fab',
        'public_ui_experience',
        'public_ui_context_scripture',
    ];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasFullPanelAccess()
            || $user?->hasAdminPermission(AdminPermission::SettingsChurch);
    }

    public function mount(): void
    {
        $this->form->fill([
            'public_ui_spark_strip' => $this->decode('public_ui_spark_strip', PublicUiCopyLibrary::sparkStrip()),
            'public_ui_divine_whispers' => $this->decode('public_ui_divine_whispers', PublicUiCopyLibrary::divineWhispers()),
            'public_ui_action_strip' => $this->decode('public_ui_action_strip', PublicUiCopyLibrary::actionStrip()),
            'public_ui_page_intro' => $this->decode('public_ui_page_intro', PublicUiCopyLibrary::pageIntro()),
            'public_ui_prayer_fab' => $this->decode('public_ui_prayer_fab', PublicUiCopyLibrary::prayerFab()),
            'public_ui_experience' => $this->decode('public_ui_experience', PublicUiCopyLibrary::experienceToggles()),
            'public_ui_context_scripture' => $this->decode('public_ui_context_scripture', PublicUiCopyLibrary::contextScripture()),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::persistBatch(function () use ($data): void {
            foreach (self::JSON_KEYS as $key) {
                Setting::set(
                    $key,
                    json_encode($data[$key] ?? [], JSON_UNESCAPED_UNICODE),
                    'public_ui',
                );
            }
        });

        SecurityLogger::logSettingsSaved('Public experience settings');

        Notification::make()
            ->success()
            ->title('Public experience saved')
            ->body('Gen Z UI blocks, Scripture layers, and performance toggles are live on the public site.')
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(PublicExperienceFormSchema::settingsTabs());
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
                                ->label('Save public experience')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    /**
     * @param  array<string, mixed>|list<array<string, mixed>>  $default
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    private function decode(string $key, array $default): array
    {
        $stored = json_decode(Setting::get($key, '[]') ?: '[]', true);

        return is_array($stored) && $stored !== [] ? $stored : $default;
    }
}
