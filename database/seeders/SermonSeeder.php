<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Database\Seeder;

class SermonSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@steciuk.org')->value('id');

        if (! $adminId) {
            throw new \RuntimeException('Admin user must be seeded before sermons.');
        }

        $sermons = [
            [
                'title' => 'Living by the Word of God',
                'speaker' => 'Rev. [Name Placeholder]',
                'preached_at' => now()->subWeeks(2)->toDateString(),
                'bible_passage' => '2 Timothy 3:16–17',
                'description' => '<p>An exposition on the authority and sufficiency of Scripture in the life of the believer, reflecting STECI\'s commitment to biblical faith.</p>',
                'youtube_url' => 'https://youtube.com/watch?v=placeholder1',
                'category' => 'Expository',
                'status' => PublishStatus::Published,
            ],
            [
                'title' => 'The Testimony of Jesus Christ',
                'speaker' => 'Rev. [Name Placeholder]',
                'preached_at' => now()->subWeeks(5)->toDateString(),
                'bible_passage' => 'Revelation 1:1–3',
                'description' => '<p>Exploring the opening words of Revelation and the call to bear witness to Jesus Christ in our daily lives.</p>',
                'youtube_url' => 'https://youtube.com/watch?v=placeholder2',
                'category' => 'Expository',
                'status' => PublishStatus::Published,
            ],
            [
                'title' => 'Rooted in Faith, Growing in Fellowship',
                'speaker' => '[Guest Speaker Placeholder]',
                'preached_at' => now()->subWeeks(8)->toDateString(),
                'bible_passage' => 'Acts 2:42–47',
                'description' => '<p>A message on the early church\'s devotion to teaching, fellowship, breaking of bread, and prayer — a model for our dispersed UK parish today.</p>',
                'youtube_url' => 'https://youtube.com/watch?v=placeholder3',
                'category' => 'Fellowship',
                'status' => PublishStatus::Published,
            ],
            [
                'title' => 'Called to Mission',
                'speaker' => 'Rev. [Name Placeholder]',
                'preached_at' => now()->subWeeks(11)->toDateString(),
                'bible_passage' => 'Matthew 28:18–20',
                'description' => '<p>The Great Commission and STECI\'s missionary calling — from Thiruvalla to the United Kingdom and beyond.</p>',
                'youtube_url' => 'https://youtube.com/watch?v=placeholder4',
                'category' => 'Mission',
                'status' => PublishStatus::Published,
            ],
        ];

        foreach ($sermons as $sermon) {
            Sermon::query()->updateOrCreate(
                [
                    'title' => $sermon['title'],
                    'preached_at' => $sermon['preached_at'],
                ],
                array_merge($sermon, [
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]),
            );
        }
    }
}
