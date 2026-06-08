<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class ChurchSettings extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Church Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Church Settings';

    protected static ?string $slug = 'church-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->form->fill([
            'church_name' => Setting::get('church_name'),
            'motto' => Setting::get('motto'),
            'contact_email' => Setting::get('contact_email'),
            'phone' => Setting::get('phone'),
            'charity_number' => Setting::get('charity_number'),
            'main_address' => Setting::get('main_address'),
            'youtube' => Setting::get('youtube'),
            'facebook' => Setting::get('facebook'),
            'instagram' => Setting::get('instagram'),
            'google_maps_embed' => Setting::get('google_maps_embed'),
            'donation_link' => Setting::get('donation_link'),
            'footer_text' => Setting::get('footer_text'),
            'seo_default_title' => Setting::get('seo_default_title'),
            'seo_default_description' => Setting::get('seo_default_description'),
            'maintenance_mode_message' => Setting::get('maintenance_mode_message'),
            'logo' => Setting::get('logo'),
            'favicon' => Setting::get('favicon'),
        ]);
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();

            foreach ($data as $key => $value) {
                Setting::set($key, $value ?? '', 'general');
            }

            Setting::forgetCache();

            $this->commitDatabaseTransaction();

            Notification::make()
                ->success()
                ->title('Settings saved')
                ->send();
        } catch (\Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Church Identity')
                    ->schema([
                        TextInput::make('church_name')
                            ->required(),
                        TextInput::make('motto'),
                        FileUpload::make('logo')
                            ->image()
                            ->directory('settings')
                            ->disk('public'),
                        FileUpload::make('favicon')
                            ->image()
                            ->directory('settings')
                            ->disk('public'),
                    ]),
                Section::make('Contact')
                    ->schema([
                        TextInput::make('contact_email')
                            ->email(),
                        TextInput::make('phone')
                            ->tel(),
                        TextInput::make('charity_number'),
                        Textarea::make('main_address')
                            ->columnSpanFull(),
                    ]),
                Section::make('Social & Links')
                    ->schema([
                        TextInput::make('youtube')
                            ->url(),
                        TextInput::make('facebook')
                            ->url(),
                        TextInput::make('instagram')
                            ->url(),
                        TextInput::make('donation_link')
                            ->url(),
                        Textarea::make('google_maps_embed')
                            ->label('Google Maps embed code')
                            ->columnSpanFull(),
                    ]),
                Section::make('Site Defaults')
                    ->schema([
                        TextInput::make('seo_default_title'),
                        Textarea::make('seo_default_description')
                            ->columnSpanFull(),
                        Textarea::make('footer_text')
                            ->columnSpanFull(),
                        Textarea::make('maintenance_mode_message')
                            ->columnSpanFull(),
                    ]),
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
