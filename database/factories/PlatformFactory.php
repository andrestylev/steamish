<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Platform>
 */
class PlatformFactory extends Factory
{
    protected $model = Platform::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Windows', 'macOS', 'Linux', 'PlayStation 5', 'Xbox Series X', 'Nintendo Switch']);

        return [
            'igdb_id' => fake()->unique()->numberBetween(1, 100000),
            'name' => $name,
            'slug' => Str::slug($name),
            'abbreviation' => fake()->optional(0.7)->lexify('???'),
        ];
    }
}
