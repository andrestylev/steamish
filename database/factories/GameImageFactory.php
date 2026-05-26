<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameImage>
 */
class GameImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'url' => fake()->imageUrl(),
            'type' => fake()->randomElement(['screenshot', 'gallery', 'background']),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
