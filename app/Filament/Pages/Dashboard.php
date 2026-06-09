<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Overview;

    protected static ?string $navigationLabel = 'Home';

    protected static ?int $navigationSort = -10;

    protected static ?string $title = 'Parish Admin Home';

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
