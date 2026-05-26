<?php

namespace Tests\Feature;

use App\Models\Game;
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

    public function test_genre_filter_works(): void
    {
        Game::factory()->create(['title' => 'Action Game 1', 'genre' => 'Action']);
        Game::factory()->create(['title' => 'Action Game 2', 'genre' => 'Action']);
        Game::factory()->create(['title' => 'RPG Game', 'genre' => 'RPG']);

        $response = $this->get('/catalog?genre=Action');

        $response->assertStatus(200);
        $response->assertSee('Action Game 1');
        $response->assertSee('Action Game 2');
        $response->assertDontSee('RPG Game');
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

    public function test_platform_filter_works(): void
    {
        Game::factory()->create(['title' => 'Windows Game', 'platforms' => ['windows', 'mac']]);
        Game::factory()->create(['title' => 'Linux Game', 'platforms' => ['linux']]);

        $response = $this->get('/catalog?platform=windows');

        $response->assertStatus(200);
        $response->assertSee('Windows Game');
        $response->assertDontSee('Linux Game');
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

    public function test_empty_results_show_no_games_found(): void
    {
        Game::factory()->create(['title' => 'Something Else', 'genre' => 'RPG']);

        $response = $this->get('/catalog?genre=Nonexistent');

        $response->assertStatus(200);
    }

    public function test_combination_of_multiple_filters_works(): void
    {
        Game::factory()->create([
            'title' => 'Matching Game',
            'genre' => 'Action',
            'price' => 19.99,
            'platforms' => ['windows', 'mac'],
            'rating_avg' => 4.5,
        ]);
        Game::factory()->create([
            'title' => 'Non Matching',
            'genre' => 'RPG',
            'price' => 59.99,
            'platforms' => ['linux'],
            'rating_avg' => 3.0,
        ]);

        $response = $this->get('/catalog?genre=Action&min_price=10&max_price=30&platform=windows&min_rating=4');

        $response->assertStatus(200);
        $response->assertSee('Matching Game');
        $response->assertDontSee('Non Matching');
    }
}
