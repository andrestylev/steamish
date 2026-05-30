<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameRelationsTest extends TestCase
{
    use RefreshDatabase;

    // --- BelongsToMany Relations ---

    public function test_game_belongs_to_many_genres(): void
    {
        $game = Game::factory()->create();
        $genre = Genre::factory()->create();
        $game->genres()->attach($genre);

        $this->assertTrue($game->genres->contains($genre));
        $this->assertCount(1, $game->genres);
    }

    public function test_game_belongs_to_many_platforms(): void
    {
        $game = Game::factory()->create();
        $platform = Platform::factory()->create();
        $game->platforms()->attach($platform);

        // Use method call to avoid collision with platforms column cast
        $platformsRelation = $game->platforms()->get();

        $this->assertTrue($platformsRelation->contains($platform));
        $this->assertCount(1, $platformsRelation);
    }

    public function test_game_belongs_to_many_tags(): void
    {
        $game = Game::factory()->create();
        $tag = Tag::factory()->create();
        $game->tags()->attach($tag);

        $this->assertTrue($game->tags->contains($tag));
        $this->assertCount(1, $game->tags);
    }

    public function test_game_belongs_to_many_companies_with_role(): void
    {
        $game = Game::factory()->create();
        $company = Company::factory()->create();
        $game->companies()->attach($company, ['role' => 'developer']);

        $this->assertTrue($game->companies->contains($company));
        $this->assertEquals('developer', $game->companies->first()->pivot->role);
    }

    // --- Scope: byGenre (pivot-first, legacy-fallback) ---

    public function test_by_genre_scope_uses_pivot_when_populated(): void
    {
        // Create game first (no genres exist yet — afterCreating attaches nothing)
        $game = Game::factory()->create(['genre' => 'Action']);
        $genre = Genre::factory()->create(['name' => 'Action']);
        $game->genres()->attach($genre);

        $results = Game::byGenre('Action')->get();

        $this->assertTrue($results->contains($game));
    }

    public function test_by_genre_scope_falls_back_to_legacy_column_when_pivot_empty(): void
    {
        Game::factory()->create(['genre' => 'Action', 'title' => 'Action Game']);
        Game::factory()->create(['genre' => 'RPG', 'title' => 'RPG Game']);

        $results = Game::byGenre('Action')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Action Game', $results->first()->title);
    }

    // --- Scope: byPlatform (pivot-first, legacy-fallback) ---

    public function test_by_platform_scope_uses_pivot_when_populated(): void
    {
        // Create game first (no platforms exist yet — afterCreating attaches nothing)
        $game = Game::factory()->create(['platforms' => '["windows"]']);
        $platform = Platform::factory()->create(['name' => 'Windows']);
        $game->platforms()->attach($platform);

        $results = Game::byPlatform('Windows')->get();

        $this->assertTrue($results->contains($game));
    }

    public function test_by_platform_scope_falls_back_to_legacy_column_when_pivot_empty(): void
    {
        Game::factory()->create(['platforms' => '["windows"]', 'title' => 'Win Game']);
        Game::factory()->create(['platforms' => '["linux"]', 'title' => 'Linux Game']);

        $results = Game::byPlatform('windows')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Win Game', $results->first()->title);
    }

    // --- IGDB fillable fields exist ---

    public function test_igdb_fields_are_fillable(): void
    {
        $game = Game::factory()->create([
            'igdb_id' => 555,
            'aggregated_rating' => 85.5,
            'storyline' => 'An epic tale',
            'status' => 'released',
        ]);

        $this->assertEquals(555, $game->igdb_id);
        $this->assertEqualsWithDelta(85.5, (float) $game->aggregated_rating, 0.1);
        $this->assertEquals('An epic tale', $game->storyline);
        $this->assertEquals('released', $game->status);
    }

    public function test_genre_scope_returns_empty_when_no_match(): void
    {
        Game::factory()->create(['genre' => 'RPG']);

        $results = Game::byGenre('Nonexistent')->get();

        $this->assertCount(0, $results);
    }
}
