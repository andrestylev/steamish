<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    use HasGameData;

    /**
     * Show the game detail page.
     */
    public function show(string $slug): Response
    {
        $hasDbData = Game::count() > 0;

        if ($hasDbData) {
            /** @var Game|null $game */
            $game = Game::with(['images', 'reviews.user', 'platforms', 'genres', 'companies'])->where('slug', $slug)->first();

            if (! $game) {
                abort(404);
            }

            $gameArray = $game->toArray();
            // Map normalized pivot relations to frontend-compatible format
            $gameArray['platforms'] = $game->platforms->pluck('slug')->toArray();
            $gameArray['genre'] = $game->genres->pluck('name')->implode(', ');
            $gameArray['developer'] = $game->companies->where('pivot.role', 'developer')->pluck('name')->first() ?? $gameArray['developer'] ?? 'Unknown';
            $gameArray['publisher'] = $game->companies->where('pivot.role', 'publisher')->pluck('name')->first() ?? $gameArray['publisher'] ?? 'Unknown';
            // Map description to about (IGDB sync fills description)
            $gameArray['about'] = $gameArray['about'] ?? $gameArray['description'] ?? 'No description available.';
            // Fallback for system requirements (missing in IGDB sync)
            if (empty($gameArray['min_req'])) {
                $gameArray['min_req'] = $this->randomMinReq();
            }
            if (empty($gameArray['rec_req'])) {
                $gameArray['rec_req'] = $this->randomRecReq();
            }
            // Fallback for ratings (IGDB sync may not set these)
            $gameArray['rating_avg'] ??= 0;
            $gameArray['rating_count'] ??= 0;
            // Use cover as fallback for header
            $gameArray['header'] = $gameArray['header'] ?? $gameArray['cover'] ?? 'https://placehold.co/1200x400/2a475e/1a9fff?text=No+Image';
            // Fallback for release date (format as Y-m-d)
            $gameArray['release_date'] = $game->release_date?->format('Y-m-d') ?? 'TBA';
            // Add gallery from game images
            $gameArray['gallery'] = $game->images->sortBy('sort_order')->pluck('url')->toArray();
            // Add screenshots from images for compatibility
            $gameArray['screenshots'] = $gameArray['gallery'];

            // Reviews with user info
            $reviews = $game->reviews->map(function (Review $review) {
                return [
                    'id' => $review->id,
                    'user' => [
                        'name' => $review->user?->name ?? 'Unknown',
                        'avatar' => $review->user?->avatar,
                    ],
                    'rating' => $review->rating,
                    'body' => $review->body,
                    'hours_played' => $review->hours_played,
                    'is_recommended' => $review->is_recommended,
                    'created_at' => $review->created_at?->toISOString(),
                ];
            })->toArray();
        } else {
            // Fallback to hardcoded data
            $games = $this->allGames();
            $gameArray = collect($games)->firstWhere('slug', $slug);

            if (! $gameArray) {
                abort(404);
            }

            // Add gallery from screenshots
            $gameArray['gallery'] = $gameArray['screenshots'] ?? [];

            // Sample reviews
            $reviews = $this->sampleReviews($gameArray['id']);
        }

        return Inertia::render('GameDetail', [
            'game' => $gameArray,
            'reviews' => $reviews,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sampleReviews(int $gameId): array
    {
        $reviewPool = [
            [
                'id' => 1,
                'user' => ['name' => 'PixelWarrior', 'avatar' => null],
                'rating' => 5,
                'body' => 'Absolutely incredible game! The graphics are stunning and the gameplay is incredibly smooth. I have sunk over 100 hours and I am still finding new things to explore.',
                'hours_played' => 120,
                'is_recommended' => true,
                'created_at' => '2025-01-15T10:30:00Z',
            ],
            [
                'id' => 2,
                'user' => ['name' => 'GameMaster42', 'avatar' => null],
                'rating' => 4,
                'body' => 'Really solid game with great mechanics. The story is engaging and keeps you hooked. Would give it 5 stars if the multiplayer had better matchmaking.',
                'hours_played' => 85,
                'is_recommended' => true,
                'created_at' => '2025-02-03T14:20:00Z',
            ],
            [
                'id' => 3,
                'user' => ['name' => 'RetroGamer99', 'avatar' => null],
                'rating' => 3,
                'body' => 'Decent game but feels a bit unpolished in places. Some bugs that should have been caught in QA. The core gameplay loop is fun though.',
                'hours_played' => 45,
                'is_recommended' => true,
                'created_at' => '2025-02-20T09:15:00Z',
            ],
            [
                'id' => 4,
                'user' => ['name' => 'SpeedRunnerX', 'avatar' => null],
                'rating' => 5,
                'body' => 'Game of the year material right here. Everything from the soundtrack to the level design is top notch. Cannot recommend this enough.',
                'hours_played' => 200,
                'is_recommended' => true,
                'created_at' => '2025-03-01T18:45:00Z',
            ],
            [
                'id' => 5,
                'user' => ['name' => 'CasualPlayer', 'avatar' => null],
                'rating' => 4,
                'body' => 'Great game for both casual and hardcore players. The difficulty curve is well balanced and there is plenty of content for the price.',
                'hours_played' => 30,
                'is_recommended' => true,
                'created_at' => '2025-03-10T11:00:00Z',
            ],
        ];

        // Use gameId as a seed to pick consistent reviews
        $offset = $gameId % count($reviewPool);
        $count = min(3, count($reviewPool));

        return array_slice($reviewPool, $offset, $count);
    }

    private function randomMinReq(): string
    {
        $oss = ['Windows 10 64-bit', 'Windows 11 64-bit'];
        $cpus = ['Intel Core i5-8400 / AMD Ryzen 5 2600', 'Intel Core i5-4590 / AMD FX-8350', 'Intel Core i3-6100 / AMD Ryzen 3 1200'];
        $rams = ['8 GB', '6 GB', '12 GB'];
        $gpus = ['NVIDIA GeForce GTX 1060 / AMD Radeon RX 580', 'NVIDIA GeForce GTX 960 / AMD Radeon R9 280', 'Intel HD Graphics 630 / AMD Radeon Vega 8'];
        $storages = ['50 GB SSD', '30 GB HDD', '80 GB SSD', '20 GB HDD'];

        return 'OS: ' . $oss[array_rand($oss)]
            . ' | CPU: ' . $cpus[array_rand($cpus)]
            . ' | RAM: ' . $rams[array_rand($rams)]
            . ' | GPU: ' . $gpus[array_rand($gpus)]
            . ' | Storage: ' . $storages[array_rand($storages)];
    }

    private function randomRecReq(): string
    {
        $oss = ['Windows 11 64-bit', 'Windows 10 64-bit'];
        $cpus = ['Intel Core i7-10700K / AMD Ryzen 7 3700X', 'Intel Core i7-9700K / AMD Ryzen 5 3600', 'Intel Core i7-7700K / AMD Ryzen 5 2600X'];
        $rams = ['16 GB', '12 GB', '32 GB'];
        $gpus = ['NVIDIA GeForce RTX 2070 / AMD Radeon RX 6700 XT', 'NVIDIA GeForce RTX 2060 / AMD Radeon RX 5600 XT', 'NVIDIA GeForce RTX 3080 / AMD Radeon RX 6800 XT'];
        $storages = ['50 GB SSD', '100 GB SSD', '80 GB NVMe SSD'];

        return 'OS: ' . $oss[array_rand($oss)]
            . ' | CPU: ' . $cpus[array_rand($cpus)]
            . ' | RAM: ' . $rams[array_rand($rams)]
            . ' | GPU: ' . $gpus[array_rand($gpus)]
            . ' | Storage: ' . $storages[array_rand($storages)];
    }
}
