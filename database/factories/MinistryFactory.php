<?php

namespace Database\Factories;

use App\Models\Ministry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ministry>
 */
class MinistryFactory extends Factory
{
    protected $model = Ministry::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => null,
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'sort_order' => 0,
            'status' => 'active',
        ];
    }
}
