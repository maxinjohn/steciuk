<?php

namespace App\Filament\Resources\Donations\Pages;

use App\Enums\DonationStatus;
use App\Filament\Resources\Donations\DonationResource;
use App\Services\DonationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDonation extends CreateRecord
{
    protected static string $resource = DonationResource::class;

    protected static ?string $title = 'Record donation';

    protected function getRedirectUrl(): string
    {
        return DonationResource::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        unset($data['family_id']);

        return app(DonationService::class)->recordManual(
            auth()->user(),
            $data,
            approveImmediately: ($data['status'] ?? null) === DonationStatus::Approved->value,
        );
    }
}
