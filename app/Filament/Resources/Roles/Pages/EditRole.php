<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Enums\AdminPermission;
use App\Filament\Resources\Roles\RoleResource;
use App\Models\Setting;
use App\Services\PermissionService;
use App\Services\SecurityLogger;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->grants_full_access) {
            $data['permissions'] = [];

            return $data;
        }

        $permissions = app(PermissionService::class)->rolePermissions($this->record->slug);
        $data['permissions'] = array_keys(array_filter($permissions));

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['permissions']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->grants_full_access) {
            return;
        }

        $selected = collect($this->form->getState()['permissions'] ?? [])
            ->flip()
            ->map(fn () => true)
            ->all();

        $labels = array_keys(AdminPermission::labels());
        $matrix = [
            $this->record->slug => collect($labels)
                ->mapWithKeys(fn (string $key) => [$key => isset($selected[$key])])
                ->all(),
        ];

        app(PermissionService::class)->saveRolePermissions($matrix);
        Setting::forgetCache();

        SecurityLogger::info('role_permissions_updated', auth()->id(), [
            'role' => $this->record->slug,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->record->is_system && $this->record->users()->count() === 0),
        ];
    }
}
