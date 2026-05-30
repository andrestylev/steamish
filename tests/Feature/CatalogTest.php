<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_page_loads_successfully(): void
    {
        $response = $this->get('/catalog');

        $response->assertStatus(200);
    }

    public function test_search_by_name_returns_matching_games(): void
    {
        Game::factory()->create(['title' => 'Cyberpunk 2077']);
        Game::factory()->create(['title' => 'Starfield']);
        Game::factory()->create(['title' => 'The Witcher 3']);

        $response = $this->get('/catalog?search=cyber');

        $response->assertStatus(200);
        $response->assertSee('Cyberpunk 2077');
        $response->assertDontSee('Starfield');
    }

    public function test_genre_filter_works_via_pivot(): void
    {
        // Create games before genres so afterCreating attaches nothing
        $game1 = Game::factory()->create(['title' => 'Action Game 1', 'genre' => 'Unrelated']);
        $game2 = Game::factory()->create(['title' => 'Action Game 2', 'genre' => 'Unrelated']);
        $game3 = Game::factory()->create(['title' => 'RPG Game', 'genre' => 'Unrelated']);

        $genre = Genre::factory()->create(['name' => 'Action', 'slug' => 'action']);
        Genre::factory()->create(['name' => 'RPG', 'slug' => 'rpg']);

        $game1->genres()->attach($genre);
        $game2->genres()->attach($genre);

        $response = $this->get('/catalog?genre=Action');

        $response->assertStatus(200);
        $response->assertSee('Action Game 1');
        $response->assertSee('Action Game 2');
        $response->assertDontSee('RPG Game');
    }

    public function test_platform_filter_works_via_pivot(): void
    {
        // Create games before platforms so afterCreating attaches nothing
        $game1 = Game::factory()->create(['title' => 'Windows Game', 'platforms' => '["linux"]']);
        $game2 = Game::factory()->create(['title' => 'Linux Game', 'platforms' => '["linux"]']);

        $platform = Platform::factory()->create(['name' => 'Windows', 'slug' => 'windows']);
        Platform::factory()->create(['name' => 'Linux', 'slug' => 'linux']);

        $game1->platforms()->attach($platform);

        $response = $this->get('/catalog?platform=windows');

        $response->assertStatus(200);
        $response->assertSee('Windows Game');
        $response->assertDontSee('Linux Game');
    }

    public function test_price_range_filter_works(): void
    {
        Game::factory()->create(['title' => 'Cheap Game', 'price' => 5.99]);
        Game::factory()->create(['title' => 'Mid Game', 'price' => 25.00]);
        Game::factory()->create(['title' => 'Expensive Game', 'price' => 59.99]);

        $response = $this->get('/catalog?min_price=10&max_price=30');

        $response->assertStatus(200);
        $response->assertDontSee('Cheap Game');
        $response->assertSee('Mid Game');
        $response->assertDontSee('Expensive Game');
    }

    public function test_rating_filter_works(): void
    {
        Game::factory()->create(['title' => 'Highly Rated', 'rating_avg' => 4.5]);
        Game::factory()->create(['title' => 'Low Rated', 'rating_avg' => 2.0]);

        $response = $this->get('/catalog?min_rating=4');

        $response->assertStatus(200);
        $response->assertSee('Highly Rated');
        $response->assertDontSee('Low Rated');
    }

    public function test_combination_of_multiple_filters_works_via_pivot(): void
    {
        // Create games first so afterCreating attaches nothing
        $matchingGame = Game::factory()->create([
            'title' => 'Matching Game',
            'genre' => 'Unrelated',
            'price' => 19.99,
            'platforms' => '["linux"]',
            'rating_avg' => 4.5,
        ]);

        Game::factory()->create([
            'title' => 'Non Matching',
            'genre' => 'Unrelated',
            'price' => 59.99,
            'platforms' => '["linux"]',
            'rating_avg' => 3.0,
        ]);

        $actionGenre = Genre::factory()->create(['name' => 'Action', 'slug' => 'action']);
        $rpgGenre = Genre::factory()->create(['name' => 'RPG', 'slug' => 'rpg']);
        $windowsPlatform = Platform::factory()->create(['name' => 'Windows', 'slug' => 'windows']);
        Platform::factory()->create(['name' => 'Linux', 'slug' => 'linux']);

        $matchingGame->genres()->attach($actionGenre);
        $nonMatching = Game::where('title', 'Non Matching')->first();
        $nonMatching->genres()->attach($rpgGenre);

        $matchingGame->platforms()->attach($windowsPlatform);

        $response = $this->get('/catalog?genre=Action&min_price=10&max_price=30&platform=windows&min_rating=4');

        $response->assertStatus(200);
        $response->assertSee('Matching Game');
        $response->assertDontSee('Non Matching');
    }

    public function test_genre_list_comes_from_genre_model(): void
    {
        Genre::factory()->create(['name' => 'Action', 'slug' => 'action']);
        Genre::factory()->create(['name' => 'RPG', 'slug' => 'rpg']);

        $response = $this->get('/catalog');

        $response->assertStatus(200);
        $response->assertSee('Action');
        $response->assertSee('RPG');
    }

    public function test_platform_list_comes_from_platform_model(): void
    {
        Platform::factory()->create(['name' => 'Windows', 'slug' => 'windows']);
        Platform::factory()->create(['name' => 'macOS', 'slug' => 'macos']);

        $response = $this->get('/catalog');

        $response->assertStatus(200);
        $response->assertSee('Windows');
        $response->assertSee('macOS');
    }

    public function test_genre_list_empty_when_no_genres(): void
    {
        $response = $this->get('/catalog');

        $response->assertStatus(200);
        $response->assertSee('"genres":[]');
    }

    public function test_platform_list_empty_when_no_platforms(): void
    {
        $response = $this->get('/catalog');

        $response->assertStatus(200);
        $response->assertSee('"platforms":[]');
    }

    public function test_genre_filter_returns_empty_when_no_match(): void
    {
        // Create game before genre so afterCreating attaches nothing
        $game = Game::factory()->create(['title' => 'RPG Only']);
        $genre = Genre::factory()->create(['name' => 'Action']);
        $game->genres()->attach(Genre::factory()->create(['name' => 'RPG']));

        $response = $this->get('/catalog?genre=Action');

        $response->assertStatus(200);
        $response->assertDontSee('RPG Only');
    }
}
