<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'slug' => null,
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'published_at' => now(),
            'status' => PublishStatus::Draft,
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => PublishStatus::Published,
            'published_at' => now(),
        ]);
    }
}
