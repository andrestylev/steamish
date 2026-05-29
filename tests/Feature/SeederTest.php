<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_runs_without_games_table_empty(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 8); // demo + 7
        $this->assertGreaterThan(0, Game::count());
        $this->assertGreaterThan(0, Genre::count());
        $this->assertGreaterThan(0, Platform::count());
    }

    public function test_seeder_attaches_genres_and_platforms_via_pivots(): void
    {
        $this->seed();

        $game = Game::first();

        $this->assertNotNull($game);
        $this->assertGreaterThan(0, $game->genres()->count(), 'Game should have at least one genre attached via pivot');
        $this->assertGreaterThan(0, $game->platforms()->count(), 'Game should have at least one platform attached via pivot');
    }

    public function test_seeder_creates_reviews_and_purchases(): void
    {
        $this->seed();

        $demoUser = User::where('email', 'demo@steamish.test')->first();
        $this->assertNotNull($demoUser);

        $this->assertGreaterThan(0, Purchase::where('user_id', $demoUser->id)->count(), 'Demo user should have purchases');
        $this->assertGreaterThan(0, Review::count(), 'At least one review should exist');
    }
}
