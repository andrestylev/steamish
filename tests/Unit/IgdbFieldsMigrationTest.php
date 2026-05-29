<?php

namespace Tests\Unit;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IgdbFieldsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_games_table_has_igdb_id_column(): void
    {
        $this->assertTrue(Schema::hasColumn('games', 'igdb_id'));
    }

    public function test_games_table_has_aggregated_rating_column(): void
    {
        $this->assertTrue(Schema::hasColumn('games', 'aggregated_rating'));
    }

    public function test_games_table_has_storyline_column(): void
    {
        $this->assertTrue(Schema::hasColumn('games', 'storyline'));
    }

    public function test_games_table_has_status_column(): void
    {
        $this->assertTrue(Schema::hasColumn('games', 'status'));
    }

    public function test_genre_remains_nullable(): void
    {
        $this->assertTrue(Schema::hasColumn('games', 'genre'));

        $game = Game::factory()->create(['genre' => null]);

        $this->assertNull($game->genre);
    }

    public function test_igdb_id_is_unique(): void
    {
        Game::factory()->create(['igdb_id' => 100]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Game::factory()->create(['igdb_id' => 100]);
    }

    public function test_down_migration_removes_igdb_fields(): void
    {
        Artisan::call('migrate:rollback', ['--step' => 1]);

        $this->assertFalse(Schema::hasColumn('games', 'igdb_id'));
        $this->assertFalse(Schema::hasColumn('games', 'aggregated_rating'));
        $this->assertFalse(Schema::hasColumn('games', 'storyline'));
        $this->assertFalse(Schema::hasColumn('games', 'status'));
    }

    public function test_original_columns_survive_migration(): void
    {
        $expectedOriginal = ['title', 'slug', 'price', 'genre', 'platforms', 'cover', 'rating_avg'];
        foreach ($expectedOriginal as $column) {
            $this->assertTrue(Schema::hasColumn('games', $column), "Column {$column} should exist");
        }
    }

    public function test_platforms_remains_nullable(): void
    {
        $game = Game::factory()->create(['platforms' => null]);

        $this->assertNull($game->platforms);
    }
}
