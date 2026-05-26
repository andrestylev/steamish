<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->game = Game::factory()->create([
            'title' => 'Test Game',
            'slug' => 'test-game',
            'price' => 29.99,
            'rating_avg' => 0,
            'rating_count' => 0,
        ]);
    }

    public function test_guest_cannot_submit_review(): void
    {
        $this->post(route('reviews.store', $this->game), [
            'rating' => 5,
            'body' => 'Great game!',
        ])->assertRedirect(route('login'));
    }

    public function test_user_must_own_game_to_review(): void
    {
        $this->actingAs($this->user)
            ->post(route('reviews.store', $this->game), [
                'rating' => 5,
                'body' => 'Great game!',
            ])
            ->assertSessionHas('error');
    }

    public function test_user_can_submit_review_after_purchase(): void
    {
        Purchase::factory()->create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('reviews.store', $this->game), [
                'rating' => 4,
                'body' => 'Really enjoyed this game!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'rating' => 4,
            'is_recommended' => true,
        ]);

        // Check rating stats were updated
        $this->game->refresh();
        $this->assertEquals(4.0, $this->game->rating_avg);
        $this->assertEquals(1, $this->game->rating_count);
    }

    public function test_user_cannot_submit_duplicate_review(): void
    {
        Purchase::factory()->create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
        ]);

        // Submit first review
        $this->actingAs($this->user)
            ->post(route('reviews.store', $this->game), [
                'rating' => 5,
                'body' => 'Amazing!',
            ]);

        // Try duplicate
        $this->actingAs($this->user)
            ->post(route('reviews.store', $this->game), [
                'rating' => 3,
                'body' => 'Changed my mind.',
            ])
            ->assertSessionHas('error');

        // Still only one review
        $this->assertEquals(1, Review::where('user_id', $this->user->id)
            ->where('game_id', $this->game->id)
            ->count());
    }

    public function test_review_requires_valid_rating(): void
    {
        Purchase::factory()->create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
        ]);

        // Rating too low
        $this->actingAs($this->user)
            ->post(route('reviews.store', $this->game), [
                'rating' => 0,
                'body' => 'Bad.',
            ])
            ->assertSessionHasErrors('rating');

        // Rating too high
        $this->actingAs($this->user)
            ->post(route('reviews.store', $this->game), [
                'rating' => 6,
                'body' => 'Overrated.',
            ])
            ->assertSessionHasErrors('rating');
    }

    public function test_review_requires_body(): void
    {
        Purchase::factory()->create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('reviews.store', $this->game), [
                'rating' => 4,
                'body' => '',
            ])
            ->assertSessionHasErrors('body');
    }

    public function test_low_rating_is_not_recommended(): void
    {
        Purchase::factory()->create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('reviews.store', $this->game), [
                'rating' => 2,
                'body' => 'Not great.',
            ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'is_recommended' => false,
        ]);
    }
}
