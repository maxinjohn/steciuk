<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Filament\Support\SecureFileUpload;
use App\Filament\Support\SettingsFormTabs;
use App\Filament\Support\UkAddressFormSchema;
use App\Models\Setting;
use App\Services\SecurityLogger;
use App\Support\UkAddressFormatter;
use App\Support\UkPostcode;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Illuminate\Support\Facades\DB;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ChurchSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SiteSettings;

    protected static ?string $navigationLabel = 'Church & faith';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Church & Faith Settings';

    protected static ?string $slug = 'church-settings';

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
            'contact_address_line_1' => Setting::get('contact_address_line_1'),
            'contact_address_line_2' => Setting::get('contact_address_line_2'),
            'contact_city' => Setting::get('contact_city'),
            'contact_county' => Setting::get('contact_county'),
            'contact_postcode' => Setting::get('contact_postcode'),
            'contact_country' => Setting::get('contact_country', 'United Kingdom'),
            'youtube' => Setting::get('youtube'),
            'facebook' => Setting::get('facebook'),
            'instagram' => Setting::get('instagram'),
            'twitter' => Setting::get('twitter'),
            'google_maps_embed' => Setting::get('google_maps_embed'),
            'footer_text' => Setting::get('footer_text'),
            'seo_default_title' => Setting::get('seo_default_title'),
            'seo_default_description' => Setting::get('seo_default_description'),
            'seo_default_og_image' => Setting::get('seo_default_og_image'),
            'theme_color' => Setting::get('theme_color', '#d4cabb'),
            'pwa_short_name' => Setting::get('pwa_short_name', 'STECI UK'),
            'registration_captcha_enabled' => Setting::get('registration_captcha_enabled', '1') !== '0',
            'admin_use_church_logo' => Setting::get('admin_use_church_logo', '1') !== '0',
            'gospel_reminder_kicker' => Setting::get('gospel_reminder_kicker'),
            'gospel_reminder_reference' => Setting::get('gospel_reminder_reference', 'Revelation 1:9'),
            'admin_welcome_heading' => Setting::get('admin_welcome_heading'),
            'admin_welcome_body' => Setting::get('admin_welcome_body'),
            'admin_dashboard_verse' => Setting::get('admin_dashboard_verse'),
            'admin_dashboard_verse_ref' => Setting::get('admin_dashboard_verse_ref'),
            'logo' => Setting::get('logo'),
            'favicon' => Setting::get('favicon'),
            'contact_office_heading' => Setting::get('contact_office_heading'),
            'contact_office_intro' => Setting::get('contact_office_intro'),
            'contact_form_heading' => Setting::get('contact_form_heading'),
            'contact_form_intro' => Setting::get('contact_form_intro'),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $data['contact_postcode'] = UkPostcode::normalize($data['contact_postcode'] ?? '')
            ?? trim((string) ($data['contact_postcode'] ?? ''));
        $data['contact_country'] = filled($data['contact_country'] ?? null)
            ? trim((string) $data['contact_country'])
            : 'United Kingdom';
        $data['main_address'] = UkAddressFormatter::format(
            line1: $data['contact_address_line_1'] ?? null,
            line2: $data['contact_address_line_2'] ?? null,
            city: $data['contact_city'] ?? null,
            county: $data['contact_county'] ?? null,
            postcode: $data['contact_postcode'] ?? null,
            country: $data['contact_country'] ?? null,
        );

        DB::transaction(function () use ($data): void {
            Setting::persistBatch(function () use ($data): void {
                foreach ($data as $key => $value) {
                    if (in_array($key, ['admin_use_church_logo', 'registration_captcha_enabled'], true)) {
                        Setting::set($key, ($value ?? false) ? '1' : '0', $key === 'admin_use_church_logo' ? 'branding' : 'security');

                        continue;
                    }

                    $group = match (true) {
                        str_starts_with($key, 'contact_') => 'contact',
                        str_starts_with($key, 'gospel_') => 'general',
                        str_starts_with($key, 'admin_') => 'admin',
                        str_starts_with($key, 'seo_') => 'seo',
                        in_array($key, ['theme_color', 'pwa_short_name'], true) => 'branding',
                        in_array($key, ['twitter', 'youtube', 'facebook', 'instagram'], true) => 'social',
                        default => 'general',
                    };

                    Setting::set($key, $value ?? '', $group);
                }
            });

            if (filled($data['logo'] ?? null)) {
                \App\Support\SiteBrandingAssets::processUploadedLogo($data['logo']);
            }
        });

        SecurityLogger::logSettingsSaved('Church & Faith settings');

        Notification::make()
            ->success()
            ->title('Settings saved')
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
                SettingsFormTabs::make('Church settings', [
                    Tab::make('Identity')
                        ->icon('heroicon-o-building-library')
                        ->schema([
                            Section::make('Church Identity')
                                ->description('Your parish name, motto, and logo shown on the public website.')
                                ->schema([
                                    TextInput::make('church_name')
                                        ->required(),
                                    TextInput::make('motto'),
                                    SecureFileUpload::image('logo', 'settings/branding', 2048),
                                    SecureFileUpload::image('favicon', 'settings/branding', 512),
                                    Toggle::make('admin_use_church_logo')
                                        ->label('Use church logo in admin sidebar')
                                        ->helperText('When on, your uploaded logo appears in the admin panel header instead of the default cross mark.')
                                        ->default(true),
                                ]),
                        ]),
                    Tab::make('Contact')
                        ->icon('heroicon-o-phone')
                        ->schema([
                            Section::make('Parish contact details')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('contact_email')
                                        ->label('Email address')
                                        ->email(),
                                    TextInput::make('phone')
                                        ->label('Phone number')
                                        ->tel()
                                        ->placeholder('e.g. 07700 900123')
                                        ->helperText('UK format — include the leading 0 for mobile and landline numbers.'),
                                    TextInput::make('charity_number')
                                        ->label('Charity number'),
                                    ...UkAddressFormSchema::fields(),
                                ]),
                            Section::make('Contact page copy')
                                ->schema([
                                    TextInput::make('contact_office_heading')
                                        ->default('Parish Office'),
                                    Textarea::make('contact_office_intro')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    TextInput::make('contact_form_heading')
                                        ->default('Send a Message'),
                                    Textarea::make('contact_form_intro')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make('Social')
                        ->icon('heroicon-o-share')
                        ->schema([
                            Section::make('Social & Links')
                                ->schema([
                                    TextInput::make('youtube')
                                        ->url(),
                                    TextInput::make('facebook')
                                        ->url(),
                                    TextInput::make('instagram')
                                        ->url(),
                                    TextInput::make('twitter')
                                        ->label('X / Twitter')
                                        ->url(),
                                    Textarea::make('google_maps_embed')
                                        ->label('Google Maps embed code')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make('Site')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Section::make('SEO & branding')
                                ->schema([
                                    TextInput::make('seo_default_title'),
                                    Textarea::make('seo_default_description')
                                        ->columnSpanFull(),
                                    SecureFileUpload::image('seo_default_og_image', 'settings/seo', 4096)
                                        ->label('Default social share image')
                                        ->helperText('Used when a page has no custom Open Graph image.'),
                                    TextInput::make('theme_color')
                                        ->label('Browser theme colour')
                                        ->placeholder('#d4cabb')
                                        ->helperText('PWA and mobile browser bar colour — use a hex code.'),
                                    TextInput::make('pwa_short_name')
                                        ->label('App short name')
                                        ->maxLength(12)
                                        ->helperText('Shown when the site is installed on a phone home screen.'),
                                    Textarea::make('footer_text')
                                        ->columnSpanFull(),
                                ]),
                            Section::make('Site behaviour')
                                ->schema([
                                    Toggle::make('registration_captcha_enabled')
                                        ->label('Registration security check (CAPTCHA)')
                                        ->helperText('Cloudflare Turnstile on member registration and the public contact form. Turn off for local testing or if the widget is unavailable. You can also set TURNSTILE_ENABLED=false in .env to disable it on this server.')
                                        ->default(true),
                                ]),
                        ]),
                    Tab::make('Gospel bar')
                        ->icon('heroicon-o-book-open')
                        ->schema([
                            Section::make('Gospel reminder bar')
                                ->description('The witness strip shown above the footer on every page.')
                                ->schema([
                                    TextInput::make('gospel_reminder_kicker')
                                        ->label('Kicker line')
                                        ->placeholder('For the Word of God · and the testimony of Jesus Christ'),
                                    TextInput::make('gospel_reminder_reference')
                                        ->label('Scripture reference')
                                        ->default('Revelation 1:9'),
                                ]),
                        ]),
                    Tab::make('Admin')
                        ->icon('heroicon-o-computer-desktop')
                        ->schema([
                            Section::make('Admin dashboard copy')
                                ->description('Welcome banner on the admin home screen — keep it warm and encouraging.')
                                ->schema([
                                    TextInput::make('admin_welcome_heading')
                                        ->label('Welcome heading')
                                        ->default('Welcome — manage your parish with peace')
                                        ->columnSpanFull(),
                                    Textarea::make('admin_welcome_body')
                                        ->label('Welcome message')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    TextInput::make('admin_dashboard_verse')
                                        ->label('Dashboard verse')
                                        ->default('Be still, and know that I am God.'),
                                    TextInput::make('admin_dashboard_verse_ref')
                                        ->label('Verse reference')
                                        ->default('Psalm 46:10'),
                                ]),
                        ]),
                ], 'church-tab'),
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
