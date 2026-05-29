<?php

namespace Tests\Unit;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NormalizedTablesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_genres_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('genres'));
        $this->assertTrue(Schema::hasColumns('genres', ['id', 'igdb_id', 'name', 'slug', 'created_at', 'updated_at']));
    }

    public function test_platforms_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('platforms'));
        $this->assertTrue(Schema::hasColumns('platforms', ['id', 'igdb_id', 'name', 'slug', 'abbreviation', 'created_at', 'updated_at']));
    }

    public function test_tags_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('tags'));
        $this->assertTrue(Schema::hasColumns('tags', ['id', 'igdb_id', 'name', 'slug', 'created_at', 'updated_at']));
    }

    public function test_companies_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('companies'));
        $this->assertTrue(Schema::hasColumns('companies', ['id', 'igdb_id', 'name', 'slug', 'country', 'created_at', 'updated_at']));
    }

    public function test_game_genre_pivot_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('game_genre'));
        $this->assertTrue(Schema::hasColumns('game_genre', ['game_id', 'genre_id']));
    }

    public function test_game_platform_pivot_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('game_platform'));
        $this->assertTrue(Schema::hasColumns('game_platform', ['game_id', 'platform_id']));
    }

    public function test_game_tag_pivot_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('game_tag'));
        $this->assertTrue(Schema::hasColumns('game_tag', ['game_id', 'tag_id']));
    }

    public function test_game_company_pivot_table_exists_with_role_column(): void
    {
        $this->assertTrue(Schema::hasTable('game_company'));
        $this->assertTrue(Schema::hasColumns('game_company', ['game_id', 'company_id', 'role']));
    }

    public function test_igdb_id_unique_constraint_prevents_duplicates(): void
    {
        DB::table('genres')->insert(['igdb_id' => 1, 'name' => 'Action', 'slug' => 'action']);
        DB::table('genres')->insert(['igdb_id' => 2, 'name' => 'RPG', 'slug' => 'rpg']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('genres')->insert(['igdb_id' => 1, 'name' => 'Duplicate', 'slug' => 'duplicate']);
    }

    public function test_down_migration_drops_all_normalized_tables(): void
    {
        // FreshDatabase runs the up migration. Run the down (rollback) and check.
        // Roll back both Phase 1 migrations (igdb_fields, then normalized_tables)
        Artisan::call('migrate:rollback', ['--step' => 2]);

        $this->assertFalse(Schema::hasTable('game_company'));
        $this->assertFalse(Schema::hasTable('game_tag'));
        $this->assertFalse(Schema::hasTable('game_platform'));
        $this->assertFalse(Schema::hasTable('game_genre'));
        $this->assertFalse(Schema::hasTable('companies'));
        $this->assertFalse(Schema::hasTable('tags'));
        $this->assertFalse(Schema::hasTable('platforms'));
        $this->assertFalse(Schema::hasTable('genres'));
    }

    public function test_cascade_delete_on_game_genre_removes_pivot_rows(): void
    {
        $game = Game::factory()->create();
        $genreId = DB::table('genres')->insertGetId(['igdb_id' => 1, 'name' => 'Action', 'slug' => 'action']);
        DB::table('game_genre')->insert(['game_id' => $game->id, 'genre_id' => $genreId]);

        $this->assertEquals(1, DB::table('game_genre')->count());

        $game->delete();

        $this->assertEquals(0, DB::table('game_genre')->count());
    }
}
