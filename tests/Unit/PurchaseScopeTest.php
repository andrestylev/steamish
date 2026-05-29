<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_monthly_sales_returns_grouped_data(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        // Create purchases across different months
        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 29.99,
            'created_at' => '2025-01-15 10:00:00',
        ]);
        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 49.99,
            'created_at' => '2025-01-20 14:00:00',
        ]);
        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 19.99,
            'created_at' => '2025-02-10 08:00:00',
        ]);

        $results = Purchase::monthlySales()->get();

        $this->assertCount(2, $results);
    }

    public function test_scope_monthly_sales_returns_correct_totals(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 100.00,
            'created_at' => '2025-01-15 10:00:00',
        ]);
        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 50.00,
            'created_at' => '2025-02-10 08:00:00',
        ]);

        $results = Purchase::monthlySales()->get();

        $this->assertEquals(100.00, (float) $results->firstWhere('month', '2025-01')->total);
        $this->assertEquals(50.00, (float) $results->firstWhere('month', '2025-02')->total);
    }

    public function test_scope_monthly_sales_returns_empty_when_no_purchases(): void
    {
        $results = Purchase::monthlySales()->get();

        $this->assertCount(0, $results);
    }

    public function test_scope_monthly_sales_uses_sqlite_driver_format(): void
    {
        $driver = DB::connection()->getDriverName();

        $this->assertEquals('sqlite', $driver);
    }

    public function test_scope_monthly_sales_orders_by_month_ascending(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 10.00,
            'created_at' => '2025-03-01 00:00:00',
        ]);
        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 20.00,
            'created_at' => '2025-01-01 00:00:00',
        ]);

        $results = Purchase::monthlySales()->get();

        $this->assertEquals('2025-01', $results[0]->month);
        $this->assertEquals('2025-03', $results[1]->month);
    }

    public function test_scope_monthly_sales_groups_same_month_purchases(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 25.00,
            'created_at' => '2025-06-01 00:00:00',
        ]);
        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 75.00,
            'created_at' => '2025-06-15 12:00:00',
        ]);

        $results = Purchase::monthlySales()->get();

        $this->assertCount(1, $results);
        $this->assertEquals(100.00, (float) $results->first()->total);
    }

    public function test_scope_monthly_sales_handles_year_boundary(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 50.00,
            'created_at' => '2024-12-31 23:59:59',
        ]);
        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 50.00,
            'created_at' => '2025-01-01 00:00:00',
        ]);

        $results = Purchase::monthlySales()->get();

        $this->assertCount(2, $results);
        $this->assertEquals('2024-12', $results[0]->month);
        $this->assertEquals('2025-01', $results[1]->month);
    }
}
