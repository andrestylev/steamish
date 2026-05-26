<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $purchase = Purchase::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($purchase->user->is($user));
        $this->assertInstanceOf(User::class, $purchase->user);
    }

    public function test_purchase_belongs_to_game(): void
    {
        $game = Game::factory()->create();
        $purchase = Purchase::factory()->create(['game_id' => $game->id]);

        $this->assertTrue($purchase->game->is($game));
        $this->assertInstanceOf(Game::class, $purchase->game);
    }

    public function test_amount_paid_cast_returns_decimal(): void
    {
        $purchase = Purchase::factory()->create(['amount_paid' => 39.99]);

        $this->assertIsString($purchase->amount_paid);
        $this->assertEquals('39.99', $purchase->amount_paid);
    }

    public function test_stripe_session_id_is_unique(): void
    {
        Purchase::factory()->create(['stripe_session_id' => 'cs_test_unique']);
        $this->expectException(\Illuminate\Database\QueryException::class);
        Purchase::factory()->create(['stripe_session_id' => 'cs_test_unique']);
    }
}
