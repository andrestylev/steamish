<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IgdbSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.igdb.client_id', 'test-client-id');
        Config::set('services.igdb.client_secret', 'test-client-secret');
        Config::set('cache.default', 'array');
    }

    public function test_aborts_when_credentials_missing(): void
    {
        Config::set('services.igdb.client_id', null);

        $this->artisan('igdb:sync')
            ->expectsOutputToContain('IGDB client credentials are not configured')
            ->assertExitCode(1);
    }

    public function test_syncs_genres(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => Http::response([
                ['id' => 1, 'name' => 'Action', 'slug' => 'action'],
                ['id' => 2, 'name' => 'RPG', 'slug' => 'rpg'],
            ], 200),
            'https://api.igdb.com/v4/*' => Http::response([], 200),
        ]);

        $this->artisan('igdb:sync')
            ->assertExitCode(0);

        $this->assertEquals(2, Genre::count());
        $this->assertEquals('Action', Genre::where('igdb_id', 1)->first()->name);
        $this->assertEquals('RPG', Genre::where('igdb_id', 2)->first()->name);
    }

    public function test_syncs_platforms(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/platforms' => Http::response([
                ['id' => 1, 'name' => 'PC', 'slug' => 'pc', 'abbreviation' => 'PC'],
            ], 200),
            'https://api.igdb.com/v4/*' => Http::response([], 200),
        ]);

        $this->artisan('igdb:sync')
            ->assertExitCode(0);

        $this->assertEquals(1, Platform::count());
        $this->assertEquals('PC', Platform::where('igdb_id', 1)->first()->name);
    }

    public function test_syncs_companies(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/companies' => Http::response([
                ['id' => 1, 'name' => 'Valve', 'slug' => 'valve', 'country' => 1],
            ], 200),
            'https://api.igdb.com/v4/*' => Http::response([], 200),
        ]);

        $this->artisan('igdb:sync')
            ->assertExitCode(0);

        $this->assertEquals(1, Company::count());
        $this->assertEquals('Valve', Company::where('igdb_id', 1)->first()->name);
    }

    public function test_syncs_games_and_attaches_relations(): void
    {
        $genre = Genre::factory()->create(['igdb_id' => 1, 'name' => 'Action']);
        $platform = Platform::factory()->create(['igdb_id' => 1, 'name' => 'PC']);
        $company = Company::factory()->create(['igdb_id' => 1, 'name' => 'Valve']);

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => Http::response([
                ['id' => 1, 'name' => 'Action', 'slug' => 'action'],
            ], 200),
            'https://api.igdb.com/v4/platforms' => Http::response([
                ['id' => 1, 'name' => 'PC', 'slug' => 'pc', 'abbreviation' => 'PC'],
            ], 200),
            'https://api.igdb.com/v4/companies' => Http::response([
                ['id' => 1, 'name' => 'Valve', 'slug' => 'valve', 'country' => 1],
            ], 200),
            'https://api.igdb.com/v4/games' => Http::response([
                [
                    'id' => 1,
                    'name' => 'Test Game',
                    'slug' => 'test-game',
                    'genres' => [1],
                    'platforms' => [1],
                    'involved_companies' => [1],
                    'aggregated_rating' => 85.0,
                    'storyline' => 'An epic tale',
                    'status' => 'released',
                ],
            ], 200),
        ]);

        $this->artisan('igdb:sync')
            ->assertExitCode(0);

        $game = Game::where('igdb_id', 1)->first();
        $this->assertNotNull($game);
        $this->assertEquals('Test Game', $game->title);
        $this->assertEquals(85.0, (float) $game->aggregated_rating);
        $this->assertEquals('An epic tale', $game->storyline);

        // Verify pivot entries exist
        $this->assertCount(1, $game->genres()->pluck('genre_id'));
        $this->assertCount(1, $game->platforms()->pluck('platform_id'));
        $this->assertCount(1, $game->companies()->pluck('company_id'));

        // Verify the related models
        $this->assertEquals($genre->id, $game->genres()->first()->id);
        $this->assertEquals($platform->id, $game->platforms()->first()->id);
        $this->assertEquals($company->id, $game->companies()->first()->id);
    }

    public function test_upserts_on_igdb_id(): void
    {
        Genre::factory()->create(['igdb_id' => 1, 'name' => 'Old Name']);

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => Http::response([
                ['id' => 1, 'name' => 'Updated Name', 'slug' => 'updated'],
            ], 200),
            'https://api.igdb.com/v4/*' => Http::response([], 200),
        ]);

        $this->artisan('igdb:sync')
            ->assertExitCode(0);

        $this->assertEquals(1, Genre::count());
        $this->assertEquals('Updated Name', Genre::first()->name);
    }

    public function test_game_limit_option(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/*' => Http::response([], 200),
        ]);

        $this->artisan('igdb:sync', ['--game-limit' => 50])
            ->assertExitCode(0);
    }

    public function test_fresh_option_truncates_then_syncs(): void
    {
        Genre::factory()->create(['igdb_id' => 99, 'name' => 'Old Genre']);

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => Http::response([
                ['id' => 1, 'name' => 'Fresh Genre', 'slug' => 'fresh'],
            ], 200),
            'https://api.igdb.com/v4/*' => Http::response([], 200),
        ]);

        $this->artisan('igdb:sync', ['--fresh' => true])
            ->assertExitCode(0);

        $this->assertEquals(1, Genre::count());
        $this->assertEquals('Fresh Genre', Genre::first()->name);
    }

    public function test_shows_progress_output(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => Http::response([], 200),
            'https://api.igdb.com/v4/platforms' => Http::response([], 200),
            'https://api.igdb.com/v4/companies' => Http::response([], 200),
            'https://api.igdb.com/v4/games' => Http::response([], 200),
        ]);

        $this->artisan('igdb:sync')
            ->expectsOutputToContain('genres')
            ->expectsOutputToContain('platforms')
            ->expectsOutputToContain('companies')
            ->expectsOutputToContain('games')
            ->assertExitCode(0);
    }
}
