<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Support\UserName;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = UserName::normalize($data);
        $data['role'] = auth()->user()?->resolveRoleForCreate($data['role'] ?? UserRole::Member->value)
            ?? UserRole::Member->value;
        $data['account_status'] = AccountStatus::Approved->value;
        $data['approved_at'] = now();
        $data['approved_by'] = auth()->id();
        $data['email_verified_at'] = now();

        if (blank($data['email'] ?? null)) {
            return $data;
        }

        $data['email'] = strtolower(trim((string) $data['email']));

        return $data;
    }
}
