<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AdminWelcomeWidget extends Widget
{
    protected static ?int $sort = -10;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.admin-welcome';

    protected static bool $isLazy = true;

    public static function isLazy(): bool
    {
        return app()->runningUnitTests() ? false : static::$isLazy;
    }
}
