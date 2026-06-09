<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AdminQuickLinksWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.admin-quick-links';

    protected static bool $isLazy = false;
}
