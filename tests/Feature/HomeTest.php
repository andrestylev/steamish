<?php

namespace Tests\Feature;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_contains_genres_from_model(): void
    {
        Genre::factory()->create(['name' => 'Genre-Unique-A', 'slug' => 'genre-unique-a']);
        Genre::factory()->create(['name' => 'Genre-Unique-B', 'slug' => 'genre-unique-b']);

        $response = $this->get('/');

        $response->assertStatus(200);
        // Old controller doesn't pass genres — would NOT see these unique names
        // New controller passes Genre::all() — MUST see them
        $response->assertSee('Genre-Unique-A');
        $response->assertSee('Genre-Unique-B');
    }

    public function test_home_page_handles_empty_genres(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('"genres":[]');
    }
}
