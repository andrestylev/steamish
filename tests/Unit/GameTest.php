<?php

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\Game;
use App\Models\GameImage;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_has_many_reviews(): void
    {
        $game = Game::factory()->create();
        $review = Review::factory()->create(['game_id' => $game->id]);

        $this->assertTrue($game->reviews->contains($review));
        $this->assertInstanceOf(Review::class, $game->reviews->first());
        $this->assertEquals(1, $game->reviews->count());
    }

    public function test_game_has_many_purchases(): void
    {
        $game = Game::factory()->create();
        $purchase = Purchase::factory()->create(['game_id' => $game->id]);

        $this->assertTrue($game->purchases->contains($purchase));
        $this->assertInstanceOf(Purchase::class, $game->purchases->first());
        $this->assertEquals(1, $game->purchases->count());
    }

    public function test_game_has_many_cart_items(): void
    {
        $game = Game::factory()->create();
        $cartItem = CartItem::factory()->create(['game_id' => $game->id]);

        $this->assertTrue($game->cartItems->contains($cartItem));
        $this->assertInstanceOf(CartItem::class, $game->cartItems->first());
        $this->assertEquals(1, $game->cartItems->count());
    }

    public function test_game_has_many_wishlist_items(): void
    {
        $game = Game::factory()->create();
        $wishlistItem = WishlistItem::factory()->create(['game_id' => $game->id]);

        $this->assertTrue($game->wishlistItems->contains($wishlistItem));
        $this->assertInstanceOf(WishlistItem::class, $game->wishlistItems->first());
        $this->assertEquals(1, $game->wishlistItems->count());
    }

    public function test_game_has_many_images(): void
    {
        $game = Game::factory()->create();
        $image = GameImage::factory()->create(['game_id' => $game->id]);

        $this->assertTrue($game->images->contains($image));
        $this->assertInstanceOf(GameImage::class, $game->images->first());
        $this->assertEquals(1, $game->images->count());
    }

    public function test_price_cast_returns_decimal(): void
    {
        $game = Game::factory()->create(['price' => 59.99]);

        $this->assertIsString($game->price);
        $this->assertEquals('59.99', $game->price);
    }

    public function test_discounted_scope_returns_only_discounted_games(): void
    {
        Game::factory()->create(['is_discounted' => true]);
        Game::factory()->create(['is_discounted' => true]);
        Game::factory()->create(['is_discounted' => false]);

        $discounted = Game::discounted()->get();

        $this->assertEquals(2, $discounted->count());
        $discounted->each(fn ($game) => $this->assertTrue($game->is_discounted));
    }

    public function test_by_genre_scope_filters_by_genre_string(): void
    {
        Game::factory()->create(['genre' => 'Action']);
        Game::factory()->create(['genre' => 'Action']);
        Game::factory()->create(['genre' => 'RPG']);

        $actionGames = Game::byGenre('Action')->get();

        $this->assertEquals(2, $actionGames->count());
        $actionGames->each(fn ($game) => $this->assertEquals('Action', $game->genre));
    }

    public function test_search_scope_does_like_query_on_title(): void
    {
        Game::factory()->create(['title' => 'Cyberpunk 2077']);
        Game::factory()->create(['title' => 'Starfield']);
        Game::factory()->create(['title' => 'The Witcher 3']);

        $results = Game::search('cyber')->get();

        $this->assertEquals(1, $results->count());
        $this->assertEquals('Cyberpunk 2077', $results->first()->title);
    }

    public function test_search_scope_is_case_insensitive(): void
    {
        Game::factory()->create(['title' => 'Grand Adventure']);
        Game::factory()->create(['title' => 'Adventure Time']);

        $results = Game::search('grand')->get();

        $this->assertEquals(1, $results->count());
        $this->assertEquals('Grand Adventure', $results->first()->title);
    }

    public function test_by_platform_scope_uses_where_json_contains(): void
    {
        Game::factory()->create(['platforms' => '["windows","mac"]']);
        Game::factory()->create(['platforms' => '["windows","linux"]']);
        Game::factory()->create(['platforms' => '["playstation","xbox"]']);

        $windowsGames = Game::byPlatform('windows')->get();

        $this->assertEquals(2, $windowsGames->count());
    }
}
