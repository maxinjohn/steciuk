<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Enums\AdminPermission;
use App\Filament\Resources\Roles\RoleResource;
use App\Models\Setting;
use App\Services\PermissionService;
use App\Services\SecurityLogger;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?? $data['name'] ?? 'role');
        $data['is_system'] = false;
        $data['grants_full_access'] = false;
        $data['sort_order'] = 100;

        unset($data['permissions']);

        return $data;
    }

    protected function afterCreate(): void
    {
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

        SecurityLogger::info('role_created', auth()->id(), [
            'role' => $this->record->slug,
        ]);
    }
}
