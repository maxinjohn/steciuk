<?php

use App\Models\Setting;
use App\Services\ParishEmailService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $givingDefaults = [
        'give_page_heading' => 'Support our parish',
        'give_page_intro' => 'Your generous giving supports worship, pastoral care, and gospel mission across our UK parish.',
        'give_anonymous_intro' => 'You can give anonymously by bank transfer using the parish account details below. No account or personal details are required.',
        'give_member_intro' => 'Sign in to report a gift you have already made and view your approved giving history in your member account.',
        'give_payment_reference' => 'Surname + Giving',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        app(ParishEmailService::class)->seedDefaultsIfMissing();

        foreach ($this->givingDefaults as $key => $value) {
            $setting = Setting::query()->where('key', $key)->first();

            if ($setting === null) {
                Setting::set($key, $value, 'giving');

                continue;
            }

            if (trim((string) $setting->value) === '') {
                $setting->update(['value' => $value, 'group' => 'giving']);
            }
        }

        $donationLink = Setting::query()->where('key', 'donation_link')->first();

        if ($donationLink === null) {
            Setting::set('donation_link', '/give', 'general');
        } elseif (in_array(trim((string) $donationLink->value), ['', 'https://steciuk.org/give'], true)) {
            $donationLink->update(['value' => '/give']);
        }

        Setting::forgetCache();
    }

    public function down(): void
    {
        // Default seeding is not reverted.
    }
};
