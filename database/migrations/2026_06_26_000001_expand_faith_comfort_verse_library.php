<?php

use App\Models\Setting;
use App\Support\FaithVerseLibrary;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Setting::query()->updateOrCreate(
            ['key' => 'faith_sanctuary_verses'],
            [
                'value' => json_encode(FaithVerseLibrary::all(), JSON_UNESCAPED_UNICODE),
                'group' => 'faith',
            ],
        );

        Setting::forgetCache();
    }

    public function down(): void
    {
        // Prefilled verse library is forward-only via migrate.
    }
};
