<?php

use App\Models\MenuItem;
use App\Models\Setting;
use App\Support\GivingUrl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $donationLink = Setting::query()->where('key', 'donation_link')->first();

        if ($donationLink === null) {
            Setting::set('donation_link', '/give', 'general');
        } elseif (GivingUrl::pointsToGivePage((string) $donationLink->value)) {
            $donationLink->update(['value' => '/give']);
        }

        if (Schema::hasTable('menu_items')) {
            MenuItem::query()
                ->whereNotNull('url')
                ->get(['id', 'url', 'is_external', 'target'])
                ->each(function (MenuItem $item): void {
                    if (! GivingUrl::pointsToGivePage($item->url)) {
                        return;
                    }

                    $item->update([
                        'url' => '/give',
                        'is_external' => false,
                        'target' => null,
                    ]);
                });
        }

        Setting::forgetCache();
    }

    public function down(): void
    {
        // Giving link repair is not reverted.
    }
};
