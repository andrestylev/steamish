<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Game;
use App\Models\GameImage;
use App\Models\Genre;
use App\Models\Platform;
use App\Services\IgdbClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class IgdbSyncCommand extends Command
{
    protected $signature = 'igdb:sync
        {--game-limit= : Number of games to sync per batch}
        {--top : Fetch top-rated games first (sorted by total_rating descending)}
        {--fresh : Truncate existing data before syncing}';

    protected $description = 'Sync IGDB data into the local database';

    public function handle(): int
    {
        if (empty(Config::get('services.igdb.client_id')) || empty(Config::get('services.igdb.client_secret'))) {
            $this->error('IGDB client credentials are not configured.');

            return 1;
        }

        try {
            $client = new IgdbClient();

            if ($this->option('fresh')) {
                $this->truncateData();
            }

            $this->syncGenres($client);
            $this->syncPlatforms($client);
            $this->syncCompanies($client);
            $this->syncGames($client);
            $this->syncCovers($client);
            $this->syncScreenshots($client);

            $this->info('IGDB sync completed successfully.');

            return 0;
        } catch (\Exception $e) {
            $this->error("Sync failed: {$e->getMessage()}");

            return 1;
        }
    }

    private function truncateData(): void
    {
        GameImage::query()->delete();
        Game::query()->delete();
        Company::query()->delete();
        Platform::query()->delete();
        Genre::query()->delete();

        $this->info('Existing data truncated.');
    }

    private function syncGenres(IgdbClient $client): void
    {
        $genres = $client->genres();

        foreach ($genres as $genre) {
            Genre::updateOrCreate(
                ['igdb_id' => $genre['id']],
                [
                    'name' => $genre['name'],
                    'slug' => $genre['slug'],
                ]
            );
        }

        $this->info("Synced " . count($genres) . " genres.");
    }

    private function syncPlatforms(IgdbClient $client): void
    {
        $platforms = $client->platforms();

        foreach ($platforms as $platform) {
            Platform::updateOrCreate(
                ['igdb_id' => $platform['id']],
                [
                    'name' => $platform['name'],
                    'slug' => $platform['slug'],
                    'abbreviation' => $platform['abbreviation'] ?? null,
                ]
            );
        }

        $this->info("Synced " . count($platforms) . " platforms.");
    }

    private function syncCompanies(IgdbClient $client): void
    {
        $companies = $client->companies();

        foreach ($companies as $company) {
            Company::updateOrCreate(
                ['igdb_id' => $company['id']],
                [
                    'name' => $company['name'],
                    'slug' => $company['slug'],
                    'country' => $company['country'] ?? null,
                ]
            );
        }

        $this->info("Synced " . count($companies) . " companies.");
    }

    private function syncGames(IgdbClient $client): void
    {
        $rawGameLimit = $this->option('game-limit');
        $batchSize = $rawGameLimit ? min((int) $rawGameLimit, 500) : 500;
        $totalLimit = $rawGameLimit ? (int) $rawGameLimit : 0;
        $sort = $this->option('top') ? 'total_rating desc' : null;
        $offset = 0;
        $total = 0;
        $syncedIgdbIds = [];

        do {
            $remaining = $totalLimit > 0 ? $totalLimit - $total : 0;
            $limit = $remaining > 0 && $remaining < $batchSize ? $remaining : $batchSize;
            $games = $client->games($limit, $offset, $sort);
            $count = count($games);

            if ($count === 0) {
                break;
            }

            foreach ($games as $game) {
                $model = Game::updateOrCreate(
                    ['igdb_id' => $game['id']],
                    [
                        'title' => $game['name'],
                        'slug' => $game['slug'],
                        'description' => $game['summary'] ?? null,
                        'aggregated_rating' => $game['aggregated_rating'] ?? null,
                        'storyline' => $game['storyline'] ?? null,
                        'status' => $game['status'] ?? null,
                        'rating_avg' => isset($game['rating']) ? round($game['rating'] / 20, 2) : 0,
                        'rating_count' => $game['rating_count'] ?? 0,
                        'release_date' => isset($game['first_release_date'])
                            ? date('Y-m-d', $game['first_release_date'])
                            : null,
                    ]
                );

                $syncedIgdbIds[] = $game['id'];

                // Attach genres via pivot
                if (! empty($game['genres'])) {
                    $genreIds = Genre::whereIn('igdb_id', $game['genres'])->pluck('id');
                    $model->genres()->syncWithoutDetaching($genreIds);
                }

                // Attach platforms via pivot
                if (! empty($game['platforms'])) {
                    $platformIds = Platform::whereIn('igdb_id', $game['platforms'])->pluck('id');
                    $model->platforms()->syncWithoutDetaching($platformIds);
                }

                $total++;
            }

            $offset += $count;
        } while ($count === $batchSize && ($totalLimit === 0 || $total < $totalLimit));

        // Sync involved companies with roles for all games synced in this run
        $this->syncInvolvedCompanies($client, $syncedIgdbIds);

        $this->info("Synced {$total} games.");
    }

    /**
     * Sync involved companies with developer/publisher roles for the given game IGDB IDs.
     */
    private function syncInvolvedCompanies(IgdbClient $client, array $igdbIds): void
    {
        $chunks = array_chunk($igdbIds, 500);
        $total = 0;
        $missingCompanyIds = [];
        $allInvolved = [];

        foreach ($chunks as $chunk) {
            $involved = $client->involvedCompanies($chunk);
            $allInvolved = array_merge($allInvolved, $involved);

            foreach ($involved as $entry) {
                $game = Game::where('igdb_id', $entry['game'])->first();
                if ($game && ! Company::where('igdb_id', $entry['company'])->exists()) {
                    $missingCompanyIds[] = $entry['company'];
                }
            }
        }

        // Fetch and create missing companies in batch
        if (! empty($missingCompanyIds)) {
            $missingCompanyIds = array_unique($missingCompanyIds);
            $companyChunks = array_chunk($missingCompanyIds, 500);

            foreach ($companyChunks as $idChunk) {
                $companies = $client->companiesByIds($idChunk);

                foreach ($companies as $c) {
                    Company::firstOrCreate(
                        ['igdb_id' => $c['id']],
                        [
                            'name' => $c['name'],
                            'slug' => $c['slug'],
                            'country' => $c['country'] ?? null,
                        ]
                    );
                }
            }

            $this->info("Synced " . count($missingCompanyIds) . " new companies.");
        }

        // Second pass: attach companies with roles
        foreach ($allInvolved as $entry) {
            $game = Game::where('igdb_id', $entry['game'])->first();
            if (! $game) {
                continue;
            }

            $company = Company::where('igdb_id', $entry['company'])->first();
            if (! $company) {
                continue;
            }

            $roles = [];
            if (! empty($entry['developer'])) {
                $roles[] = 'developer';
            }
            if (! empty($entry['publisher'])) {
                $roles[] = 'publisher';
            }
            if (empty($roles)) {
                $roles[] = 'developer';
            }

            foreach ($roles as $role) {
                $game->companies()->syncWithoutDetaching([
                    $company->id => ['role' => $role],
                ]);
            }

            $total++;
        }

        if ($total > 0) {
            $this->info("Synced {$total} involved companies.");
        }
    }

    private function syncCovers(IgdbClient $client): void
    {
        $gameIds = Game::whereNotNull('igdb_id')->pluck('igdb_id')->toArray();

        if (empty($gameIds)) {
            return;
        }

        $imageId = GameImage::where('type', 'cover')->pluck('game_id')->toArray();
        $existingGames = Game::whereIn('id', $imageId)->pluck('igdb_id')->toArray();
        $gameIds = array_values(array_diff($gameIds, $existingGames));

        if (empty($gameIds)) {
            $this->info('All covers already synced.');

            return;
        }

        $chunks = array_chunk($gameIds, 100);
        $total = 0;

        foreach ($chunks as $chunk) {
            $covers = $client->covers($chunk);

            foreach ($covers as $cover) {
                $game = Game::where('igdb_id', $cover['game'])->first();
                if (! $game) {
                    continue;
                }

                $url = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $cover['image_id'] . '.jpg';

                GameImage::create([
                    'game_id' => $game->id,
                    'url' => $url,
                    'type' => 'cover',
                    'sort_order' => 0,
                ]);

                $game->update(['cover' => $url]);

                $total++;
            }
        }

        $this->info("Synced {$total} covers.");
    }

    private function syncScreenshots(IgdbClient $client): void
    {
        $gameIds = Game::whereNotNull('igdb_id')->pluck('igdb_id')->toArray();

        if (empty($gameIds)) {
            return;
        }

        $chunks = array_chunk($gameIds, 100);
        $total = 0;

        foreach ($chunks as $chunk) {
            $screenshots = $client->screenshots($chunk);

            foreach ($screenshots as $screenshot) {
                $game = Game::where('igdb_id', $screenshot['game'])->first();
                if (! $game) {
                    continue;
                }

                $url = 'https://images.igdb.com/igdb/image/upload/t_screenshot_huge/' . $screenshot['image_id'] . '.jpg';

                GameImage::create([
                    'game_id' => $game->id,
                    'url' => $url,
                    'type' => 'screenshot',
                    'sort_order' => 0,
                ]);

                $total++;
            }
        }

        $this->info("Synced {$total} screenshots.");
    }
}
