<?php

namespace Database\Seeders;

use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $albums = [
            [
                'title' => 'Parish Worship Services',
                'slug' => 'parish-worship-services',
                'description' => 'Photos from monthly worship gatherings across our UK locations.',
                'sort_order' => 1,
                'status' => 'published',
                'photos' => [
                    ['title' => 'Worship Service — Manchester', 'caption' => 'Congregation gathered for monthly worship', 'image_path' => 'gallery/placeholder-worship-manchester.jpg', 'alt_text' => 'Worship service in Manchester', 'status' => 'published'],
                    ['title' => 'Holy Communion', 'caption' => 'Celebrating Holy Communion during parish worship', 'image_path' => 'gallery/placeholder-communion.jpg', 'alt_text' => 'Holy Communion service', 'status' => 'published'],
                    ['title' => 'Hymn Singing', 'caption' => 'Congregation singing hymns of praise', 'image_path' => 'gallery/placeholder-hymns.jpg', 'alt_text' => 'Congregation singing hymns', 'status' => 'published'],
                    ['title' => 'Sermon', 'caption' => 'Biblical preaching during worship service', 'image_path' => 'gallery/placeholder-sermon.jpg', 'alt_text' => 'Sermon during worship', 'status' => 'published'],
                ],
            ],
            [
                'title' => 'Fellowship & Community Events',
                'slug' => 'fellowship-community-events',
                'description' => 'Moments from parish fellowship days, youth events, and community gatherings.',
                'sort_order' => 2,
                'status' => 'published',
                'photos' => [
                    ['title' => 'Parish Fellowship Day', 'caption' => 'Families gathered for parish fellowship', 'image_path' => 'gallery/placeholder-fellowship.jpg', 'alt_text' => 'Parish fellowship day', 'status' => 'published'],
                    ['title' => 'Shared Meal', 'caption' => 'Fellowship meal after worship', 'image_path' => 'gallery/placeholder-meal.jpg', 'alt_text' => 'Shared fellowship meal', 'status' => 'published'],
                    ['title' => 'Youth Fellowship', 'caption' => 'Young people at a youth fellowship event', 'image_path' => 'gallery/placeholder-youth.jpg', 'alt_text' => 'Youth fellowship gathering', 'status' => 'published'],
                    ['title' => 'Sunday School', 'caption' => 'Children participating in Sunday School activities', 'image_path' => 'gallery/placeholder-sunday-school.jpg', 'alt_text' => 'Sunday School activities', 'status' => 'published'],
                ],
            ],
        ];

        foreach ($albums as $albumData) {
            $photos = $albumData['photos'];
            unset($albumData['photos']);

            $album = GalleryAlbum::query()->updateOrCreate(
                ['slug' => $albumData['slug']],
                $albumData,
            );

            foreach ($photos as $index => $photo) {
                GalleryPhoto::query()->updateOrCreate(
                    [
                        'gallery_album_id' => $album->id,
                        'title' => $photo['title'],
                    ],
                    array_merge($photo, ['sort_order' => $index + 1]),
                );
            }
        }
    }
}
