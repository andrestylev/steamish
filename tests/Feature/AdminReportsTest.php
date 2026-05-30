<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_reports(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/reports');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_reports_page(): void
    {
        $admin = User::factory()->create([
            'email' => 'demo@steamish.test',
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
    }

    public function test_reports_page_loads_with_empty_state_when_no_purchases(): void
    {
        $admin = User::factory()->create([
            'email' => 'demo@steamish.test',
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
    }

    public function test_revenue_by_genre_shows_totals_from_pivot(): void
    {
        $admin = User::factory()->create(['email' => 'demo@steamish.test']);
        $user = User::factory()->create();

        // Create games first (no genres exist yet — afterCreating attaches nothing)
        $actionGame = Game::factory()->create([
            'title' => 'Action Game',
            'genre' => 'LegacyDefault',
            'price' => 29.99,
        ]);

        $rpgGame = Game::factory()->create([
            'title' => 'RPG Game',
            'genre' => 'LegacyDefault',
            'price' => 19.99,
        ]);

        // Create pivot genre records with unique names not derived from legacy column
        $actionGenre = Genre::factory()->create(['name' => 'Pivot-Action']);
        $rpgGenre = Genre::factory()->create(['name' => 'Pivot-RPG']);

        $actionGame->genres()->attach($actionGenre);
        $rpgGame->genres()->attach($rpgGenre);

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $actionGame->id,
            'amount_paid' => 29.99,
        ]);

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $rpgGame->id,
            'amount_paid' => 19.99,
        ]);

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $actionGame->id,
            'amount_paid' => 29.99,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
        // Old controller groups by games.genre ('LegacyDefault') — would NOT show 'Pivot-Action'
        // New controller joins through game_genre pivot — MUST show 'Pivot-Action'
        $response->assertSee('Pivot-Action');
        $response->assertSee('Pivot-RPG');
    }

    public function test_reports_data_shown_when_purchases_exist(): void
    {
        $admin = User::factory()->create([
            'email' => 'demo@steamish.test',
        ]);
        $user = User::factory()->create();

        // Create game before genre so afterCreating attaches nothing
        $game = Game::factory()->create([
            'title' => 'Test Game',
            'genre' => 'LegacyDefault',
            'price' => 29.99,
        ]);
        $genre = Genre::factory()->create(['name' => 'Action']);
        $game->genres()->attach($genre);

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 29.99,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
        $response->assertSee('Test Game');
    }

    public function test_revenue_by_genre_empty_when_no_genre_records(): void
    {
        $admin = User::factory()->create(['email' => 'demo@steamish.test']);
        $user = User::factory()->create();

        $game = Game::factory()->create([
            'title' => 'No Genre Game',
            'genre' => 'LegacyDefault',
            'price' => 19.99,
        ]);

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 19.99,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
    }

    public function test_monthly_sales_works_without_sqlite_errors(): void
    {
        $admin = User::factory()->create(['email' => 'demo@steamish.test']);
        $user = User::factory()->create();

        $game = Game::factory()->create(['title' => 'Any Game']);
        Purchase::factory()->count(3)->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 9.99,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
    }
}
