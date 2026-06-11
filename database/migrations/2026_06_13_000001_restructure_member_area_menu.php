<?php

use App\Database\ReferenceMenuApplicator;
use App\Services\MenuCache;
use App\Services\SiteCache;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ReferenceMenuApplicator::apply();
        MenuCache::forgetAll();
        SiteCache::forgetAfterReferenceDataChange();
    }

    public function down(): void
    {
        // Member area menu structure is not reverted on rollback.
    }
};
