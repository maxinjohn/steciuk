<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@steciuk.org')->value('id');

        if (! $adminId) {
            throw new \RuntimeException('Admin user must be seeded before events.');
        }

        $events = [
            [
                'title' => 'UK Parish Fellowship Day',
                'slug' => 'uk-parish-fellowship-day',
                'description' => '<p>Join parish families from across all five UK locations for a day of worship, fellowship, shared meal, and activities for all ages. A highlight of our parish calendar bringing together our dispersed community.</p><p>Programme details and venue to be confirmed. Please contact the parish office to register your interest.</p>',
                'starts_at' => now()->addWeeks(3)->setTime(10, 0),
                'ends_at' => now()->addWeeks(3)->setTime(16, 0),
                'location' => 'Manchester',
                'address' => 'Venue to be confirmed — contact parish office',
                'registration_required' => true,
                'registration_link' => '/contact',
                'category' => 'Fellowship',
                'status' => PublishStatus::Published,
            ],
            [
                'title' => 'Monthly Prayer Meeting',
                'slug' => 'monthly-prayer-meeting',
                'description' => '<p>Parish-wide prayer meeting held online and hosted from rotating UK locations. Join us for intercession, Scripture reading, and shared prayer for families, mission, and the church.</p>',
                'starts_at' => now()->addWeeks(1)->setTime(19, 30),
                'ends_at' => now()->addWeeks(1)->setTime(21, 0),
                'location' => 'Online',
                'address' => null,
                'registration_required' => false,
                'category' => 'Prayer',
                'status' => PublishStatus::Published,
            ],
            [
                'title' => 'Vacation Bible School (VBS)',
                'slug' => 'vacation-bible-school',
                'description' => '<p>A week of Bible stories, songs, crafts, and fun for children during the school holidays. Organised by our Sunday School ministry with volunteer teachers from across the parish.</p><p>Dates and location to be announced. Registration will open closer to the event.</p>',
                'starts_at' => now()->addMonths(2)->setTime(9, 30),
                'ends_at' => now()->addMonths(2)->addDays(4)->setTime(15, 0),
                'location' => 'Dartford',
                'address' => 'Venue to be confirmed',
                'registration_required' => true,
                'registration_link' => '/contact',
                'category' => 'Children',
                'status' => PublishStatus::Published,
            ],
            [
                'title' => 'Annual General Meeting (AGM)',
                'slug' => 'annual-general-meeting',
                'description' => '<p>The UK Parish Annual General Meeting for charity reporting, parish updates, and committee elections. All parish members are encouraged to attend.</p><p>Agenda and papers will be circulated in advance. Placeholder date — please confirm with parish secretary.</p>',
                'starts_at' => now()->addMonths(4)->setTime(14, 0),
                'ends_at' => now()->addMonths(4)->setTime(17, 0),
                'location' => 'Leicester',
                'address' => 'Venue to be confirmed',
                'registration_required' => false,
                'category' => 'Parish Business',
                'status' => PublishStatus::Published,
            ],
            [
                'title' => 'Good Friday Service',
                'slug' => 'good-friday-service',
                'description' => '<p>A solemn service of reflection on the passion and death of our Lord Jesus Christ. All are welcome to join this special liturgical observance.</p>',
                'starts_at' => now()->addMonths(3)->setTime(10, 30),
                'ends_at' => now()->addMonths(3)->setTime(12, 0),
                'location' => 'Bristol',
                'address' => 'Community Church Hall, 55 Gloucester Road, Bristol, BS7 8AD',
                'registration_required' => false,
                'category' => 'Worship',
                'status' => PublishStatus::Published,
            ],
            [
                'title' => 'Youth Fellowship Day Out',
                'slug' => 'youth-fellowship-day-out',
                'description' => '<p>A social and spiritual day out for young people from across the UK Parish. Activities, Bible study, and fellowship in an informal setting.</p><p>Details and transport arrangements to be confirmed by the Youth Fellowship leaders.</p>',
                'starts_at' => now()->addWeeks(6)->setTime(11, 0),
                'ends_at' => now()->addWeeks(6)->setTime(17, 0),
                'location' => 'Sunderland',
                'address' => 'Meeting point to be confirmed',
                'registration_required' => true,
                'registration_link' => '/contact',
                'category' => 'Youth',
                'status' => PublishStatus::Published,
            ],
        ];

        foreach ($events as $event) {
            Event::query()->updateOrCreate(
                ['slug' => $event['slug']],
                array_merge($event, [
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]),
            );
        }
    }
}
