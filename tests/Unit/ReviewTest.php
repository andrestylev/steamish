<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($review->user->is($user));
        $this->assertInstanceOf(User::class, $review->user);
    }

    public function test_review_belongs_to_game(): void
    {
        $game = Game::factory()->create();
        $review = Review::factory()->create(['game_id' => $game->id]);

        $this->assertTrue($review->game->is($game));
        $this->assertInstanceOf(Game::class, $review->game);
    }

    public function test_rating_cast_returns_integer(): void
    {
        $review = Review::factory()->create(['rating' => 4]);

        $this->assertIsInt($review->rating);
        $this->assertEquals(4, $review->rating);
    }

    public function test_is_recommended_cast_returns_boolean(): void
    {
        $review = Review::factory()->create(['is_recommended' => true]);

        $this->assertIsBool($review->is_recommended);
        $this->assertTrue($review->is_recommended);
    }

    public function test_hours_played_cast_returns_integer(): void
    {
        $review = Review::factory()->create(['hours_played' => 150]);

        $this->assertIsInt($review->hours_played);
        $this->assertEquals(150, $review->hours_played);
    }
}
