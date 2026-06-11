<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Models\Setting;
use App\Services\SecurityLogger;
use App\Support\GivingPageConfig;
use BackedEnum;
use Filament\Actions\Action;
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

class GivingSettings extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Giving;

    protected static ?string $navigationLabel = 'Giving page & bank details';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Giving page & bank details';

    protected static ?string $slug = 'giving-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasFullPanelAccess() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'give_page_heading' => Setting::get('give_page_heading', 'Support our parish'),
            'give_page_intro' => Setting::get('give_page_intro', 'Your generous giving supports worship, pastoral care, and gospel mission across our UK parish.'),
            'give_anonymous_intro' => Setting::get('give_anonymous_intro', 'You can give anonymously by bank transfer using the parish account details below. No account or personal details are required.'),
            'give_member_intro' => Setting::get('give_member_intro', 'Sign in to report a gift you have already made and view your approved giving history in your member account.'),
            'give_bank_name' => Setting::get('give_bank_name'),
            'give_account_name' => Setting::get('give_account_name'),
            'give_sort_code' => Setting::get('give_sort_code'),
            'give_account_number' => Setting::get('give_account_number'),
            'give_payment_reference' => Setting::get('give_payment_reference', 'Surname + Giving'),
            'give_payment_link' => Setting::get('give_payment_link'),
        ]);
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            Setting::persistBatch(function (): void {
                foreach ($this->form->getState() as $key => $value) {
                    Setting::set($key, $value ?? '', 'giving');
                }

                Setting::set('donation_link', '/give', 'general');
            });

            $this->commitDatabaseTransaction();

            SecurityLogger::logSettingsSaved('Giving page & bank details');

            Notification::make()
                ->success()
                ->title('Giving settings saved')
                ->body('The public /give page and member portal giving tab will use these details.')
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
        return $schema->components([
            Section::make('Public giving page')
                ->description('Shown on the /give page and linked from the header Give button.')
                ->schema([
                    TextInput::make('give_page_heading')
                        ->label('Page heading')
                        ->required()
                        ->maxLength(120)
                        ->columnSpanFull(),
                    Textarea::make('give_page_intro')
                        ->label('Page introduction')
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('give_member_intro')
                        ->label('Member giving intro')
                        ->rows(3)
                        ->helperText('Encourages signed-in members to track giving in their account.')
                        ->columnSpanFull(),
                    Textarea::make('give_anonymous_intro')
                        ->label('Anonymous giving intro')
                        ->rows(3)
                        ->helperText('Explains that no account is needed for bank transfer.')
                        ->columnSpanFull(),
                ]),
            Section::make('Bank transfer details')
                ->description('Displayed on /give and in the member portal Giving tab.')
                ->columns(2)
                ->schema([
                    TextInput::make('give_bank_name')
                        ->label('Bank name')
                        ->placeholder('e.g. Barclays')
                        ->maxLength(120),
                    TextInput::make('give_account_name')
                        ->label('Account name')
                        ->placeholder('e.g. STECI UK Parish')
                        ->maxLength(120),
                    TextInput::make('give_sort_code')
                        ->label('Sort code')
                        ->placeholder('00-00-00')
                        ->maxLength(20),
                    TextInput::make('give_account_number')
                        ->label('Account number')
                        ->maxLength(20),
                    TextInput::make('give_payment_reference')
                        ->label('Payment reference hint')
                        ->placeholder('e.g. Your surname + Giving')
                        ->maxLength(120)
                        ->columnSpanFull(),
                    TextInput::make('give_payment_link')
                        ->label('Online payment link (optional)')
                        ->url()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->helperText('Optional Stripe, PayPal, or other payment page URL.'),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('preview')
                            ->label('Preview /give page')
                            ->icon('heroicon-o-eye')
                            ->url(fn (): string => route('give'))
                            ->openUrlInNewTab(),
                        Action::make('save')
                            ->label('Save giving settings')
                            ->submit('save'),
                    ]),
                ]),
        ]);
    }
}
