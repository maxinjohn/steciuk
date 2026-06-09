<?php

namespace Database\Factories;

use App\Models\GalleryAlbum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryAlbum>
 */
class GalleryAlbumFactory extends Factory
{
    protected $model = GalleryAlbum::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'slug' => null,
            'description' => fake()->sentence(),
            'sort_order' => 0,
            'status' => 'active',
        ];
    }
}
