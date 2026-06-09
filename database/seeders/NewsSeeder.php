<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@steciuk.org')->value('id');

        if (! $adminId) {
            throw new \RuntimeException('Admin user must be seeded before news.');
        }

        $articles = [
            [
                'title' => 'Lent Prayer Week Across the UK Parish',
                'slug' => 'lent-prayer-week-uk-parish',
                'excerpt' => 'Join parish prayer groups across Manchester, Leicester, Dartford, Sunderland, and Bristol for a week of united intercession for families, mission, and the Church.',
                'content' => '<p>Our parish prayer groups invite every member to draw near to God in a dedicated week of prayer. Daily online meetings and local gatherings will be held across all five UK worship locations.</p><p>We will intercede for families, for the preaching of the Gospel, for our missionaries, and for unity in Christ across our dispersed parish family.</p><p>Contact the parish office for meeting times, online links, and local prayer group details.</p>',
                'category' => 'Prayer',
                'published_at' => now()->subDays(3),
                'status' => PublishStatus::Published,
                'seo_title' => 'Lent Prayer Week | STECI UK Parish',
                'seo_description' => 'Invitation to parish-wide prayer across UK worship locations.',
            ],
            [
                'title' => 'Monthly Worship Schedule Update',
                'slug' => 'monthly-worship-schedule-update',
                'excerpt' => 'Please contact the parish office for confirmed worship dates and times at Manchester, Leicester, Dartford, Sunderland, and Bristol.',
                'content' => '<p>Our monthly worship services continue across all five UK locations. Due to venue availability and seasonal arrangements, service dates may vary from month to month.</p><p>Please contact the parish office at <a href="mailto:admin@steciuk.org">admin@steciuk.org</a> or call <strong>07578 189530</strong> for the latest schedule before planning your visit.</p><p>Online streaming links will be shared ahead of each service where available.</p>',
                'category' => 'Worship',
                'published_at' => now()->subDays(10),
                'status' => PublishStatus::Published,
                'seo_title' => 'Monthly Worship Schedule Update | STECI UK Parish',
                'seo_description' => 'Information about worship schedules across UK parish locations.',
            ],
            [
                'title' => 'Parish Prayer Week',
                'slug' => 'parish-prayer-week',
                'excerpt' => 'Join parish prayer groups across the UK for a week of united intercession for families, mission, and the church.',
                'content' => '<p>Our parish prayer groups invite all members to participate in a dedicated week of prayer. Daily online prayer meetings and local gatherings will be held across Manchester, Leicester, Dartford, Sunderland, and Bristol.</p><p>Contact the prayer group coordinator via the parish office for meeting times and online links.</p>',
                'category' => 'Prayer',
                'published_at' => now()->subDays(17),
                'status' => PublishStatus::Published,
                'seo_title' => 'Parish Prayer Week | STECI UK Parish',
                'seo_description' => 'Invitation to join parish-wide prayer week across UK locations.',
            ],
            [
                'title' => 'Sunday School Teachers Needed',
                'slug' => 'sunday-school-teachers-needed',
                'excerpt' => 'We are looking for volunteers to help teach and support our Sunday School ministry at worship locations across the UK.',
                'content' => '<p>Our Sunday School ministry continues to grow as families join our parish. We are seeking committed volunteers who love children and wish to help them grow in knowledge of the Bible and Christian faith.</p><p>Training and resources will be provided. If you feel called to serve in this ministry, please contact the parish office or speak with your local congregation leader.</p>',
                'category' => 'Ministries',
                'published_at' => now()->subDays(24),
                'status' => PublishStatus::Published,
                'seo_title' => 'Sunday School Teachers Needed | STECI UK Parish',
                'seo_description' => 'Volunteer opportunity for Sunday School teachers across UK parish.',
            ],
        ];

        foreach ($articles as $article) {
            News::query()->updateOrCreate(
                ['slug' => $article['slug']],
                array_merge($article, [
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]),
            );
        }
    }
}
