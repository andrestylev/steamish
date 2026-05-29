<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Genre>
 */
class GenreFactory extends Factory
{
    protected $model = Genre::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'igdb_id' => fake()->unique()->numberBetween(1, 100000),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
