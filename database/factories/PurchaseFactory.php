<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'stripe_session_id' => 'cs_test_' . fake()->unique()->regexify('[a-z0-9]{24}'),
            'amount_paid' => fake()->randomFloat(2, 9.99, 69.99),
        ];
    }
}
