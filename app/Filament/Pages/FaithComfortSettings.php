<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Filament\Support\FaithComfortFormSchema;
use App\Models\Setting;
use App\Services\SecurityLogger;
use App\Support\FaithComfortVerseBuckets;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class FaithComfortSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SiteSettings;

    protected static ?string $navigationLabel = 'Faith & comfort';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Faith & Comfort';

    protected static ?string $slug = 'faith-comfort';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * @var list<string>
     */
    private const JSON_SETTINGS = [
        'faith_sanctuary_ribbons',
        'faith_comfort_headers',
        'faith_comfort_cards',
    ];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasFullPanelAccess()
            || $user?->hasAdminPermission(AdminPermission::SettingsChurch);
    }

    public function mount(): void
    {
        $storedVerses = json_decode(Setting::get('faith_sanctuary_verses', '[]') ?: '[]', true) ?: [];

        $this->form->fill(array_merge(
            FaithComfortVerseBuckets::split($storedVerses),
            [
                'faith_sanctuary_ribbons' => json_decode(Setting::get('faith_sanctuary_ribbons', '[]') ?: '[]', true) ?: [],
                'faith_comfort_headers' => json_decode(Setting::get('faith_comfort_headers', '[]') ?: '[]', true) ?: [],
                'faith_comfort_cards' => json_decode(Setting::get('faith_comfort_cards', '[]') ?: '[]', true) ?: [],
            ],
        ));
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data): void {
            Setting::persistBatch(function () use ($data): void {
                Setting::set(
                    'faith_sanctuary_verses',
                    json_encode(FaithComfortVerseBuckets::merge($data), JSON_UNESCAPED_UNICODE),
                    'faith',
                );

                foreach ($data as $key => $value) {
                    if (in_array($key, FaithComfortVerseBuckets::verseFieldNames(), true)) {
                        continue;
                    }

                    if (in_array($key, self::JSON_SETTINGS, true)) {
                        Setting::set($key, json_encode($value ?? [], JSON_UNESCAPED_UNICODE), 'faith');
                    }
                }
            });
        });

        SecurityLogger::logSettingsSaved('Faith & comfort settings');

        Notification::make()
            ->success()
            ->title('Faith & comfort saved')
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(FaithComfortFormSchema::settingsTabs());
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
