<?php

namespace App\Filament\Resources\Families\Pages;

use App\Filament\Resources\Families\FamilyResource;
use App\Models\User;
use App\Services\MemberRegistrationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFamily extends CreateRecord
{
    protected static string $resource = FamilyResource::class;

    protected ?int $headUserId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->headUserId = filled($data['head_user_id'] ?? null) ? (int) $data['head_user_id'] : null;
        unset($data['head_user_id']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $head = $this->headUserId ? User::query()->find($this->headUserId) : null;

        return app(MemberRegistrationService::class)->createFamily(
            auth()->user(),
            $data,
            $head,
        );
    }

    protected function getRedirectUrl(): string
    {
        return FamilyResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
