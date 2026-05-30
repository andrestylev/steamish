<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected static ?string $lastTitle = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 9.99, 69.99);
        $isDiscounted = fake()->boolean(20);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(2, true),
            'about' => fake()->paragraphs(4, true),
            'price' => $price,
            'discount_price' => $isDiscounted ? round($price * fake()->randomFloat(2, 0.5, 0.85), 2) : null,
            'discount_pct' => $isDiscounted ? fake()->numberBetween(15, 50) : null,
            'is_discounted' => $isDiscounted,
            'release_date' => fake()->date(),
            'developer' => fake()->company(),
            'publisher' => fake()->company(),
            'genre' => fake()->randomElement(['Action', 'RPG', 'FPS', 'Strategy', 'Sports', 'Simulation', 'Adventure', 'Puzzle']),
            'platforms' => json_encode(fake()->randomElements(['windows', 'mac', 'linux', 'playstation', 'xbox', 'nintendo'], fake()->numberBetween(1, 3))),
            'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=' . urlencode($title),
            'header' => 'https://placehold.co/1200x400/2a475e/1a9fff?text=' . urlencode($title),
            'rating_avg' => fake()->randomFloat(2, 3.0, 5.0),
            'rating_count' => fake()->numberBetween(100, 50000),
            'min_req' => fake()->sentence(6),
            'rec_req' => fake()->sentence(6),
        ];
    }

    /**
     * Configure the factory — attach random Genre/Platform/Company after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Game $game) {
            $genres = Genre::inRandomOrder()->take(rand(1, 3))->get();
            $game->genres()->attach($genres);

            $platforms = Platform::inRandomOrder()->take(rand(1, 4))->get();
            $game->platforms()->attach($platforms);

            if (fake()->boolean(70)) {
                $company = Company::inRandomOrder()->first();
                if ($company) {
                    $game->companies()->attach($company, ['role' => 'developer']);
                }
            }

            if (fake()->boolean(70) && ($publisher = Company::inRandomOrder()->first())) {
                $game->companies()->attach($publisher, ['role' => 'publisher']);
            }
        });
    }

    /**
     * Mark the game as discounted.
     */
    public function discounted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_discounted' => true,
            'discount_price' => round($attributes['price'] * fake()->randomFloat(2, 0.5, 0.85), 2),
            'discount_pct' => fake()->numberBetween(15, 50),
        ]);
    }

    /**
     * Set a specific genre.
     */
    public function ofGenre(string $genre): static
    {
        return $this->state(fn () => ['genre' => $genre]);
    }
}
