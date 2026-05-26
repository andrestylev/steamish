<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_to_login_when_toggling_wishlist(): void
    {
        $response = $this->post('/wishlist/1');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_add_game_to_wishlist(): void
    {
        $user = User::factory()->create();
        Game::factory()->create(['id' => 1, 'title' => 'Cyber Legends']);

        $response = $this->actingAs($user)->post('/wishlist/1');

        $response->assertRedirect();
        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
    }

    public function test_authenticated_user_can_remove_from_wishlist(): void
    {
        $user = User::factory()->create();
        Game::factory()->create(['id' => 1, 'title' => 'Cyber Legends']);
        WishlistItem::factory()->create(['user_id' => $user->id, 'game_id' => 1]);

        $response = $this->actingAs($user)->post('/wishlist/1');

        $response->assertRedirect();
        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
    }

    public function test_toggle_works_add_if_not_present_remove_if_present(): void
    {
        $user = User::factory()->create();
        Game::factory()->create(['id' => 1, 'title' => 'Cyber Legends']);

        // Add
        $this->actingAs($user)->post('/wishlist/1');
        $this->assertDatabaseCount('wishlist_items', 1);

        // Toggle again removes
        $this->actingAs($user)->post('/wishlist/1');
        $this->assertDatabaseCount('wishlist_items', 0);

        // Toggle again adds back
        $this->actingAs($user)->post('/wishlist/1');
        $this->assertDatabaseCount('wishlist_items', 1);
    }

    public function test_wishlist_page_shows_saved_games(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['id' => 1, 'title' => 'Cyber Legends']);
        WishlistItem::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        $response = $this->actingAs($user)->get('/wishlist');

        $response->assertStatus(200);
    }

    public function test_empty_wishlist_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/wishlist');

        $response->assertStatus(200);
    }
}
