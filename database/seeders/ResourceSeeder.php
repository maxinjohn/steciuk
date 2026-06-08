<?php

namespace Database\Seeders;

use App\Enums\ResourceCategory;
use App\Models\Resource;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            [
                'title' => 'Order of Holy Communion',
                'slug' => 'order-of-holy-communion',
                'description' => 'Standard order of service for Holy Communion used in STECI UK Parish worship.',
                'category' => ResourceCategory::Liturgy,
                'external_url' => 'https://steciuk.org/downloads/order-of-holy-communion.pdf',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Morning Prayer Service',
                'slug' => 'morning-prayer-service',
                'description' => 'Liturgy for Morning Prayer services.',
                'category' => ResourceCategory::Liturgy,
                'external_url' => 'https://steciuk.org/downloads/morning-prayer.pdf',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'title' => 'Lectionary 2026',
                'slug' => 'lectionary-2026',
                'description' => 'Scripture readings appointed for worship throughout the year.',
                'category' => ResourceCategory::Lectionary,
                'external_url' => 'https://steciuk.org/downloads/lectionary-2026.pdf',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'New Member Registration Form',
                'slug' => 'new-member-registration-form',
                'description' => 'PDF form for new parish membership registration.',
                'category' => ResourceCategory::Forms,
                'external_url' => 'https://steciuk.org/downloads/new-member-form.pdf',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Event Booking Form',
                'slug' => 'event-booking-form',
                'description' => 'Form for registering interest in parish events.',
                'category' => ResourceCategory::Forms,
                'external_url' => 'https://steciuk.org/downloads/event-booking-form.pdf',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'title' => 'Parish Notice — March 2026',
                'slug' => 'parish-notice-march-2026',
                'description' => 'Monthly parish notice with announcements and updates.',
                'category' => ResourceCategory::Notices,
                'external_url' => 'https://steciuk.org/downloads/notice-march-2026.pdf',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Annual Report 2025',
                'slug' => 'annual-report-2025',
                'description' => 'UK Parish annual report including charity accounts summary.',
                'category' => ResourceCategory::Reports,
                'external_url' => 'https://steciuk.org/downloads/annual-report-2025.pdf',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Safeguarding Policy',
                'slug' => 'safeguarding-policy',
                'description' => 'Parish safeguarding policy for children and vulnerable adults.',
                'category' => ResourceCategory::Safeguarding,
                'external_url' => 'https://steciuk.org/downloads/safeguarding-policy.pdf',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Safeguarding Reporting Procedure',
                'slug' => 'safeguarding-reporting-procedure',
                'description' => 'Step-by-step guide for reporting safeguarding concerns.',
                'category' => ResourceCategory::Safeguarding,
                'external_url' => 'https://steciuk.org/downloads/safeguarding-reporting.pdf',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'title' => 'Parish Newsletter — Spring 2026',
                'slug' => 'parish-newsletter-spring-2026',
                'description' => 'Seasonal newsletter with news from across the UK Parish.',
                'category' => ResourceCategory::Newsletters,
                'external_url' => 'https://steciuk.org/downloads/newsletter-spring-2026.pdf',
                'sort_order' => 1,
                'status' => 'active',
            ],
        ];

        foreach ($resources as $resource) {
            Resource::query()->updateOrCreate(
                ['slug' => $resource['slug']],
                $resource,
            );
        }
    }
}
