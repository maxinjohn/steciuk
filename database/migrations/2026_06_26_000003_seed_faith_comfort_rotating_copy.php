<?php

use App\Models\Setting;
use App\Support\FaithCopyLibrary;
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
            ['key' => 'faith_sanctuary_ribbons'],
            [
                'value' => json_encode(FaithCopyLibrary::sanctuaryRibbons(), JSON_UNESCAPED_UNICODE),
                'group' => 'faith',
            ],
        );

        Setting::query()->updateOrCreate(
            ['key' => 'faith_comfort_headers'],
            [
                'value' => json_encode(FaithCopyLibrary::comfortHeaders(), JSON_UNESCAPED_UNICODE),
                'group' => 'faith',
            ],
        );

        Setting::forgetCache();
    }

    public function down(): void
    {
        // Rotating ribbon and comfort header lists are forward-only via migrate.
    }
};
