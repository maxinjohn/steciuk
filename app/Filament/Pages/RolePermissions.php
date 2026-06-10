<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Models\Role;
use App\Services\PermissionService;
use App\Services\SecurityLogger;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RolePermissions extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Security;

    protected static ?string $navigationLabel = 'Access permissions';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Role Permissions';

    protected static ?string $slug = 'role-permissions';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasFullPanelAccess()
            || $user?->hasAdminPermission(AdminPermission::SettingsPermissions);
    }

    public function mount(): void
    {
        $service = app(PermissionService::class);
        $matrix = $service->allRolePermissions();
        $fill = [];

        foreach ($service->manageableRoleSlugs() as $slug) {
            $fill[$slug] = array_keys(array_filter($matrix[$slug] ?? []));
        }

        $this->form->fill($fill);
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $state = $this->form->getState();
            $labels = AdminPermission::labels();
            $allKeys = array_keys($labels);
            $matrix = [];
            $service = app(PermissionService::class);

            foreach ($service->manageableRoleSlugs() as $roleSlug) {
                $selected = collect($state[$roleSlug] ?? [])
                    ->flip()
                    ->map(fn () => true)
                    ->all();

                $matrix[$roleSlug] = collect($allKeys)
                    ->mapWithKeys(fn (string $key) => [$key => isset($selected[$key])])
                    ->all();
            }

            $service->saveRolePermissions($matrix);

            SecurityLogger::info('role_permissions_updated', auth()->id());

            $this->commitDatabaseTransaction();

            Notification::make()
                ->success()
                ->title('Role permissions saved')
                ->body('Editors and custom roles will receive these privileges on their next request.')
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
        $options = AdminPermission::labels();
        $service = app(PermissionService::class);
        $tabs = [];

        foreach ($service->manageableRoleSlugs() as $slug) {
            $roleName = Role::labelForSlug($slug);
            $tabs[] = Tab::make($roleName)
                ->schema([
                    CheckboxList::make($slug)
                        ->label($roleName.' privileges')
                        ->options($options)
                        ->columns(2)
                        ->bulkToggleable()
                        ->searchable(),
                ]);
        }

        return $schema
            ->components([
                Section::make('Super Admin')
                    ->description('Super admins always have full access. Configure privileges for other roles below, or use Roles for custom roles.')
                    ->schema([]),
                Tabs::make('Roles')
                    ->tabs($tabs),
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
                                ->label('Save permissions')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }
}
