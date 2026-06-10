<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Services\Concerns\ManagesServiceRecord;

class CreateService extends \Filament\Resources\Pages\CreateRecord
{
    use ManagesServiceRecord;

    protected static string $resource = ServiceResource::class;
}
