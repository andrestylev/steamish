<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class IgdbClient
{
    private const TOKEN_URL = 'https://id.twitch.tv/oauth2/token';
    private const API_BASE = 'https://api.igdb.com/v4';
    private const MAX_REQUESTS_PER_SECOND = 4;

    private ?string $accessToken = null;
    private array $requestTimestamps = [];

    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $clientId = Config::get('services.igdb.client_id');
        $clientSecret = Config::get('services.igdb.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            throw new \RuntimeException('IGDB client credentials are not configured. Set IGDB_CLIENT_ID and IGDB_CLIENT_SECRET in .env');
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Fetch genres from IGDB.
     *
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    public function genres(): array
    {
        return $this->get('genres', 'fields name,slug; limit 500;');
    }

    /**
     * Fetch platforms from IGDB.
     *
     * @return array<int, array{id: int, name: string, slug: string, abbreviation: ?string}>
     */
    public function platforms(): array
    {
        return $this->get('platforms', 'fields name,slug,abbreviation; limit 500;');
    }

    /**
     * Fetch companies from IGDB.
     *
     * @return array<int, array{id: int, name: string, slug: string, country: ?int}>
     */
    public function companies(): array
    {
        return $this->get('companies', 'fields name,slug,country; limit 500;');
    }

    /**
     * Fetch games from IGDB with optional limit and offset.
     *
     * @param  int  $limit  Number of games to fetch (max 500)
     * @param  int  $offset  Offset for pagination
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    public function games(int $limit = 500, int $offset = 0): array
    {
        return $this->get('games', "fields name,slug,summary,genres,platforms,involved_companies,cover,aggregated_rating,storyline,status; limit {$limit}; offset {$offset};");
    }

    /**
     * Send a POST request to the IGDB API with rate limiting, retry, and token refresh.
     *
     * @return array<int, array<string, mixed>>
     */
    private function get(string $endpoint, string $body): array
    {
        $this->rateLimit();
        $hasRefreshedToken = false;
        $serverRetries = 0;
        $rateLimitRetries = 0;

        while (true) {
            $token = $this->getAccessToken($hasRefreshedToken);

            try {
                $response = Http::withHeaders([
                    'Client-ID' => $this->clientId,
                    'Authorization' => "Bearer {$token}",
                ])
                    ->withBody($body, 'text/plain')
                    ->retry(3, 100, function (\Exception $e, $request) {
                        return $e instanceof \Illuminate\Http\Client\ConnectionException;
                    }, throw: false)
                    ->post(self::API_BASE . '/' . $endpoint);

                // Token expired — refresh and retry once
                if ($response->unauthorized() && ! $hasRefreshedToken) {
                    Cache::forget('igdb_access_token');
                    $this->accessToken = null;
                    $hasRefreshedToken = true;

                    continue;
                }

                // Server error — retry with backoff (up to 3 times)
                if ($response->serverError()) {
                    $serverRetries++;
                    if ($serverRetries < 3) {
                        usleep(100_000);
                        continue;
                    }

                    return [];
                }

                // Rate limited — wait and retry (up to 3 times)
                if ($response->status() === 429 && $rateLimitRetries < 3) {
                    $rateLimitRetries++;
                    $retryAfter = (int) ($response->header('Retry-After') ?? 1);
                    usleep($retryAfter * 1_000_000);

                    continue;
                }

                // Any other client error
                if ($response->failed()) {
                    return [];
                }

                return $response->json() ?? [];
            } catch (\Exception $e) {
                return [];
            }
        }
    }

    /**
     * Get or fetch the IGDB access token.
     */
    private function getAccessToken(bool $forceRefresh = false): string
    {
        if (! $forceRefresh && isset($this->accessToken)) {
            return $this->accessToken;
        }

        if ($forceRefresh || ! Cache::has('igdb_access_token')) {
            $this->accessToken = $this->fetchAccessToken();
            Cache::put('igdb_access_token', $this->accessToken, 5184000);
        } else {
            $this->accessToken = Cache::get('igdb_access_token');
        }

        return $this->accessToken;
    }

    /**
     * Fetch a new access token from Twitch OAuth.
     */
    private function fetchAccessToken(): string
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch IGDB access token: ' . $response->body());
        }

        $data = $response->json();

        return $data['access_token'];
    }

    /**
     * Enforce 4 requests per second rate limit.
     */
    private function rateLimit(): void
    {
        $now = microtime(true);

        $this->requestTimestamps = array_values(
            array_filter($this->requestTimestamps, fn (float $ts) => $now - $ts < 1.0)
        );

        if (count($this->requestTimestamps) >= self::MAX_REQUESTS_PER_SECOND) {
            $sleep = 1_000_000 - (int) (($now - $this->requestTimestamps[0]) * 1_000_000);
            if ($sleep > 0) {
                usleep((int) $sleep);
            }
            $this->requestTimestamps = [];
        }

        $this->requestTimestamps[] = microtime(true);
    }
}
