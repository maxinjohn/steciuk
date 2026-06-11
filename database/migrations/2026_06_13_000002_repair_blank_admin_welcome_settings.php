<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $defaults = [
        'admin_welcome_heading' => 'Welcome - manage your parish with peace',
        'admin_welcome_body' => 'On phones and tablets, use the bottom bar for Home, Worship, Events, and Menu. Tap Menu for the full sidebar, then choose a page.',
        'admin_dashboard_verse' => 'Be still, and know that I am God.',
        'admin_dashboard_verse_ref' => 'Psalm 46:10',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        foreach ($this->defaults as $key => $value) {
            $setting = Setting::query()->where('key', $key)->first();

            if ($setting === null) {
                continue;
            }

            if (trim((string) $setting->value) === '') {
                $setting->update(['value' => $value]);
            }
        }

        Setting::forgetCache();
    }

    public function down(): void
    {
        // Blank admin welcome repair is not reverted.
    }
};
