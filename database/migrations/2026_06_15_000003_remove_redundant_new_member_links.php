<?php

use App\Database\ReferenceMenuApplicator;
use App\Models\ContentBlock;
use App\Models\MenuItem;
use App\Services\MenuCache;
use App\Services\SiteCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        MenuItem::query()
            ->whereIn('seed_key', [
                'contact.new-member',
                'contact.register',
                'members.enquiry',
                'members.membership-enquiry',
            ])
            ->delete();

        ReferenceMenuApplicator::apply();

        if (Schema::hasTable('content_blocks')) {
            ContentBlock::query()->each(function (ContentBlock $block): void {
                $content = is_array($block->content) ? $block->content : [];

                if (($content['button_url'] ?? null) !== '/new-member') {
                    return;
                }

                $content['button_url'] = '/register';

                if (($content['button_label'] ?? '') === 'Register as a New Member') {
                    $content['button_label'] = 'Join the parish';
                }

                $block->update(['content' => $content]);
            });
        }

        MenuCache::forgetAll();
        SiteCache::forgetAfterReferenceDataChange();
    }

    public function down(): void
    {
        //
    }
};
