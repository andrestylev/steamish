<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected static ?array $usedTitles = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(rand(2, 4), true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'about' => fake()->paragraphs(3, true),
            'price' => fake()->randomFloat(2, 9.99, 69.99),
            'discount_price' => null,
            'discount_pct' => null,
            'is_discounted' => false,
            'release_date' => fake()->date(),
            'developer' => fake()->company(),
            'publisher' => fake()->company(),
            'genre' => fake()->randomElement([
                'Action', 'RPG', 'FPS', 'Strategy',
                'Sports', 'Simulation', 'Adventure', 'Puzzle',
            ]),
            'platforms' => fake()->randomElements(
                ['windows', 'mac', 'linux', 'playstation', 'xbox', 'nintendo'],
                rand(1, 4)
            ),
            'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=' . urlencode($title),
            'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=' . urlencode($title),
            'rating_avg' => fake()->randomFloat(2, 3.0, 5.0),
            'rating_count' => fake()->numberBetween(100, 50000),
            'min_req' => fake()->optional()->sentence(),
            'rec_req' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the game is discounted.
     */
    public function discounted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_discounted' => true,
            'discount_price' => round(fake()->randomFloat(2, 4.99, (float) $attributes['price'] * 0.75), 2),
            'discount_pct' => fake()->numberBetween(10, 75),
        ]);
    }
}
