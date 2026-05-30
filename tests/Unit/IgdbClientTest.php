<?php

namespace Tests\Unit;

use App\Services\IgdbClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IgdbClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.igdb.client_id', 'test-client-id');
        Config::set('services.igdb.client_secret', 'test-client-secret');
        Config::set('cache.default', 'array');
    }

    /** Helper: set up token endpoint fake + return the access token used */
    private function fakeToken(): string
    {
        $token = 'test-access-token';
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => $token,
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
        ]);

        return $token;
    }

    public function test_throws_exception_when_credentials_missing(): void
    {
        Config::set('services.igdb.client_id', null);
        Config::set('services.igdb.client_secret', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('IGDB client credentials are not configured');

        new IgdbClient();
    }

    public function test_throws_exception_when_client_id_missing(): void
    {
        Config::set('services.igdb.client_id', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('IGDB client credentials are not configured');

        new IgdbClient();
    }

    public function test_genres_endpoint_returns_correct_data(): void
    {
        $this->fakeToken();

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => Http::response([
                ['id' => 1, 'name' => 'Action', 'slug' => 'action'],
                ['id' => 2, 'name' => 'RPG', 'slug' => 'rpg'],
            ], 200),
        ]);

        $client = new IgdbClient();
        $genres = $client->genres();

        $this->assertCount(2, $genres);
        $this->assertEquals('Action', $genres[0]['name']);
        $this->assertEquals('rpg', $genres[1]['slug']);
    }

    public function test_platforms_endpoint_returns_correct_data(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/platforms' => Http::response([
                ['id' => 1, 'name' => 'PC', 'slug' => 'pc', 'abbreviation' => 'PC'],
                ['id' => 2, 'name' => 'PlayStation 5', 'slug' => 'ps5', 'abbreviation' => 'PS5'],
            ], 200),
        ]);

        $client = new IgdbClient();
        $platforms = $client->platforms();

        $this->assertCount(2, $platforms);
        $this->assertEquals('PC', $platforms[0]['name']);
        $this->assertEquals('PS5', $platforms[1]['abbreviation']);
    }

    public function test_companies_endpoint_returns_correct_data(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/companies' => Http::response([
                ['id' => 1, 'name' => 'Valve', 'slug' => 'valve', 'country' => 1],
            ], 200),
        ]);

        $client = new IgdbClient();
        $companies = $client->companies();

        $this->assertCount(1, $companies);
        $this->assertEquals('Valve', $companies[0]['name']);
        $this->assertEquals(1, $companies[0]['country']);
    }

    public function test_games_endpoint_with_limit_and_offset(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/games' => Http::response([
                ['id' => 1, 'name' => 'Game One', 'slug' => 'game-one'],
                ['id' => 2, 'name' => 'Game Two', 'slug' => 'game-two'],
            ], 200),
        ]);

        $client = new IgdbClient();
        $games = $client->games(2, 0);

        $this->assertCount(2, $games);
        $this->assertEquals('Game One', $games[0]['name']);
    }

    public function test_sends_client_id_and_bearer_token_headers(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => function ($request) {
                $this->assertEquals('test-client-id', $request->header('Client-ID')[0]);
                $this->assertEquals('Bearer test-access-token', $request->header('Authorization')[0]);

                return Http::response([], 200);
            },
        ]);

        $client = new IgdbClient();
        $client->genres();
    }

    public function test_sends_apicalypse_query_in_body(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => function ($request) {
                $this->assertEquals('text/plain', $request->header('Content-Type')[0]);
                $this->assertEquals('fields name,slug; limit 500;', (string) $request->body());

                return Http::response([], 200);
            },
        ]);

        $client = new IgdbClient();
        $client->genres();
    }

    public function test_caches_access_token_for_subsequent_calls(): void
    {
        $requestCount = 0;

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => function () use (&$requestCount) {
                $requestCount++;

                return Http::response([
                    'access_token' => 'test-access-token',
                    'expires_in' => 5184000,
                    'token_type' => 'bearer',
                ]);
            },
            'https://api.igdb.com/v4/*' => Http::response([], 200),
        ]);

        $client = new IgdbClient();

        $client->genres();
        $this->assertEquals(1, $requestCount);

        $client->platforms();
        $this->assertEquals(1, $requestCount);

        $client->companies();
        $this->assertEquals(1, $requestCount);
    }

    public function test_401_triggers_token_refresh_and_retry(): void
    {
        $tokenRequestCount = 0;
        $apiCallCount = 0;

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => function () use (&$tokenRequestCount) {
                $tokenRequestCount++;
                $token = $tokenRequestCount === 1 ? 'expired-token' : 'refreshed-token';

                return Http::response([
                    'access_token' => $token,
                    'expires_in' => 5184000,
                    'token_type' => 'bearer',
                ]);
            },
            'https://api.igdb.com/v4/genres' => function () use (&$apiCallCount) {
                $apiCallCount++;
                if ($apiCallCount === 1) {
                    return Http::response([], 401);
                }

                return Http::response([
                    ['id' => 1, 'name' => 'Action', 'slug' => 'action'],
                ], 200);
            },
        ]);

        $client = new IgdbClient();
        $genres = $client->genres();

        $this->assertCount(1, $genres);
        $this->assertEquals('Action', $genres[0]['name']);
        $this->assertEquals(2, $apiCallCount);
        $this->assertEquals(2, $tokenRequestCount);
    }

    public function test_retries_on_server_error_up_to_three_times(): void
    {
        $apiCallCount = 0;

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => function () use (&$apiCallCount) {
                $apiCallCount++;
                if ($apiCallCount <= 2) {
                    return Http::response([], 500);
                }

                return Http::response([
                    ['id' => 1, 'name' => 'Action', 'slug' => 'action'],
                ], 200);
            },
        ]);

        $client = new IgdbClient();
        $genres = $client->genres();

        $this->assertCount(1, $genres);
        $this->assertEquals('Action', $genres[0]['name']);
        $this->assertEquals(3, $apiCallCount);
    }

    public function test_returns_empty_array_when_all_retries_fail(): void
    {
        $apiCallCount = 0;

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => function () use (&$apiCallCount) {
                $apiCallCount++;

                return Http::response([], 500);
            },
        ]);

        $client = new IgdbClient();
        $genres = $client->genres();

        $this->assertIsArray($genres);
        $this->assertEmpty($genres);
        $this->assertEquals(3, $apiCallCount);
    }

    public function test_fetch_throws_exception_when_token_request_fails(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([], 400),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch IGDB access token');

        $client = new IgdbClient();
        $client->genres();
    }

    public function test_covers_endpoint_returns_correct_data(): void
    {
        $this->fakeToken();

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/covers' => Http::response([
                ['id' => 1, 'image_id' => 'co123', 'game' => 1],
                ['id' => 2, 'image_id' => 'co456', 'game' => 2],
            ], 200),
        ]);

        $covers = (new IgdbClient())->covers([1, 2]);

        $this->assertCount(2, $covers);
        $this->assertEquals('co123', $covers[0]['image_id']);
        $this->assertEquals(2, $covers[1]['game']);
    }

    public function test_covers_returns_empty_array_when_no_ids(): void
    {
        $this->assertSame([], (new IgdbClient())->covers([]));
    }

    public function test_screenshots_endpoint_returns_correct_data(): void
    {
        $this->fakeToken();

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/screenshots' => Http::response([
                ['id' => 1, 'image_id' => 'sc123', 'game' => 1],
            ], 200),
        ]);

        $screenshots = (new IgdbClient())->screenshots([1]);

        $this->assertCount(1, $screenshots);
        $this->assertEquals('sc123', $screenshots[0]['image_id']);
    }

    public function test_screenshots_returns_empty_array_when_no_ids(): void
    {
        $this->assertSame([], (new IgdbClient())->screenshots([]));
    }

    public function test_429_triggers_retry_after_second_wait_and_succeeds(): void
    {
        $attempts = 0;

        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/genres' => function () use (&$attempts) {
                $attempts++;
                if ($attempts === 1) {
                    return Http::response([], 429, ['Retry-After' => '1']);
                }

                return Http::response([
                    ['id' => 1, 'name' => 'Action', 'slug' => 'action'],
                ], 200);
            },
        ]);

        $genres = (new IgdbClient())->genres();

        $this->assertCount(1, $genres);
        $this->assertEquals('Action', $genres[0]['name']);
        $this->assertEquals(2, $attempts);
    }

    public function test_games_passes_limit_and_offset_in_body(): void
    {
        Http::fake([
            'https://id.twitch.tv/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 5184000,
                'token_type' => 'bearer',
            ]),
            'https://api.igdb.com/v4/games' => function ($request) {
                $body = (string) $request->body();
                $this->assertStringContainsString('limit 10;', $body);
                $this->assertStringContainsString('offset 20;', $body);

                return Http::response([['id' => 1, 'name' => 'Test']], 200);
            },
        ]);

        $client = new IgdbClient();
        $games = $client->games(10, 20);

        $this->assertCount(1, $games);
    }
}
