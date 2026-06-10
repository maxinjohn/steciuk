<?php

namespace App\Filament\Resources\Donations\Pages;

use App\Filament\Resources\Donations\DonationResource;
use App\Models\User;
use App\Services\DonationService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDonation extends EditRecord
{
    protected static string $resource = DonationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->can('delete', $this->getRecord()) ?? false)
                ->action(function (): void {
                    app(DonationService::class)->deleteFromAdmin(auth()->user(), $this->getRecord());
                    $this->redirect(DonationResource::getUrl('index'));
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        unset($data['family_id']);

        return app(DonationService::class)->updateFromAdmin(auth()->user(), $record, $data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $donor = User::query()->find($data['user_id'] ?? null);
        $data['family_id'] = $donor?->family_id;

        return $data;
    }
}
