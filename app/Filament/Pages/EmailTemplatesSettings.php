<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Filament\Support\SettingsFormTabs;
use App\Services\ParishEmailService;
use App\Services\SecurityLogger;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EmailTemplatesSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SiteSettings;

    protected static ?string $navigationLabel = 'Email templates';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Parish email templates';

    protected static ?string $slug = 'email-templates';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasFullPanelAccess()
            || $user?->hasAdminPermission(AdminPermission::SettingsMail);
    }

    public function mount(): void
    {
        $this->fillFormFromService();
    }

    public function restoreDefaults(): void
    {
        app(ParishEmailService::class)->resetTemplatesToDefaults();
        $this->fillFormFromService();

        SecurityLogger::logSettingsSaved('Email templates restored to defaults');

        Notification::make()
            ->title('Default email templates restored')
            ->success()
            ->send();
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $templates = [];

        foreach (app(ParishEmailService::class)->defaultTemplates() as $key => $defaults) {
            $templates[$key] = [
                'subject' => (string) ($data["{$key}_subject"] ?? $defaults['subject']),
                'body' => (string) ($data["{$key}_body"] ?? $defaults['body']),
            ];
        }

        app(ParishEmailService::class)->saveTemplates($templates);

        SecurityLogger::logSettingsSaved('Email templates');

        Notification::make()
            ->title('Email templates saved')
            ->success()
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    private function fillFormFromService(): void
    {
        $service = app(ParishEmailService::class);
        $service->seedDefaultsIfMissing();

        $state = [];

        foreach ($service->allTemplates() as $key => $template) {
            $state["{$key}_subject"] = $template['subject'] ?? '';
            $state["{$key}_body"] = $template['body'] ?? '';
        }

        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        $tabs = [];

        foreach (app(ParishEmailService::class)->defaultTemplates() as $key => $template) {
            $placeholderHelp = collect($template['placeholders'] ?? [])->implode(', ');

            $tabs[] = Tab::make($template['label'])
                ->schema([
                    Section::make($template['label'])
                        ->description('Placeholders: '.$placeholderHelp)
                        ->schema([
                            TextInput::make("{$key}_subject")
                                ->label('Subject')
                                ->required()
                                ->default($template['subject'])
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Textarea::make("{$key}_body")
                                ->label('Body')
                                ->rows(10)
                                ->required()
                                ->default($template['body'])
                                ->columnSpanFull(),
                        ]),
                ]);
        }

        return $schema->components([
            SettingsFormTabs::make('Templates', $tabs, 'template-tab'),
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
                            Action::make('restoreDefaults')
                                ->label('Restore defaults')
                                ->color('gray')
                                ->outlined()
                                ->requiresConfirmation()
                                ->modalHeading('Restore default email templates?')
                                ->modalDescription('This replaces every subject and body with the built-in parish defaults. Custom wording will be lost.')
                                ->action(fn () => $this->restoreDefaults()),
                            Action::make('save')
                                ->label('Save templates')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }
}
