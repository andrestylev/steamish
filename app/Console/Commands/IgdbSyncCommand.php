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
        $gameLimit = $this->option('game-limit');
        $limit = $gameLimit ? (int) $gameLimit : 500;
        $offset = 0;
        $total = 0;

        do {
            $games = $client->games($limit, $offset);
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
                    ]
                );

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

                // Attach companies via pivot
                if (! empty($game['involved_companies'])) {
                    $companyIds = Company::whereIn('igdb_id', $game['involved_companies'])->pluck('id');
                    $model->companies()->syncWithoutDetaching($companyIds);
                }

                $total++;
            }

            $offset += $count;
        } while ($count === $limit);

        $this->info("Synced {$total} games.");
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
