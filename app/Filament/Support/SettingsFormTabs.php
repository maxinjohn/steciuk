<?php

namespace App\Filament\Support;

use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class SettingsFormTabs
{
    /**
     * @param  array<Tab>  $tabs
     */
    public static function make(string $label, array $tabs, string $queryKey = 'tab'): Tabs
    {
        return Tabs::make($label)
            ->tabs($tabs)
            ->scrollable()
            ->contained(false)
            ->persistTabInQueryString($queryKey)
            ->extraAttributes(['class' => 'admin-form-tabs settings-form-tabs'])
            ->columnSpanFull();
    }
}
