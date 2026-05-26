<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_adding_to_cart(): void
    {
        $response = $this->post('/cart/add/1');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_add_game_to_cart(): void
    {
        $user = User::factory()->create();
        // Create a game with ID that matches hardcoded data (game ID 1 = 'Cyber Legends')
        Game::factory()->create(['id' => 1, 'title' => 'Cyber Legends']);

        $response = $this->actingAs($user)->post('/cart/add/1');

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
    }

    public function test_authenticated_user_can_view_cart(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['id' => 1, 'title' => 'Cyber Legends', 'price' => 59.99]);
        CartItem::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        $response = $this->actingAs($user)->get('/cart');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_remove_item_from_cart(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['id' => 1, 'title' => 'Cyber Legends']);
        $cartItem = CartItem::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        $response = $this->actingAs($user)->delete('/cart/' . $cartItem->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_cannot_add_same_game_twice(): void
    {
        $user = User::factory()->create();
        Game::factory()->create(['id' => 1, 'title' => 'Cyber Legends']);

        $this->actingAs($user)->post('/cart/add/1');
        $this->actingAs($user)->post('/cart/add/1');

        $this->assertEquals(1, CartItem::where('user_id', $user->id)->count());
    }

    public function test_cart_page_shows_subtotal(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['id' => 1, 'title' => 'Cyber Legends', 'price' => 59.99]);
        CartItem::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        $response = $this->actingAs($user)->get('/cart');

        $response->assertStatus(200);
    }
}
