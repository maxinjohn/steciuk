<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Public layout data is shared once per request via ShareSiteLayoutData middleware.
    }
}
