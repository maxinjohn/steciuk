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

    protected static ?int $navigationSort = 4;

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
        $service = app(ParishEmailService::class);
        $service->seedDefaultsIfMissing();

        $templates = $service->allTemplates();
        $state = [];

        foreach ($templates as $key => $template) {
            $state["{$key}_subject"] = $template['subject'] ?? '';
            $state["{$key}_body"] = $template['body'] ?? '';
        }

        $this->form->fill($state);
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
                            Action::make('save')
                                ->label('Save templates')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }
}
