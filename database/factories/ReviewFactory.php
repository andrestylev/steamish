<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = fake()->numberBetween(1, 5);

        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'rating' => $rating,
            'body' => fake()->paragraph(),
            'hours_played' => fake()->numberBetween(0, 500),
            'is_recommended' => $rating >= 3,
        ];
    }
}
