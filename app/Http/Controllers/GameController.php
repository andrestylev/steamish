<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $games = $this->allGames();
        $game = collect($games)->firstWhere('slug', $slug);

        if (! $game) {
            abort(404);
        }

        // Simulate gallery images from screenshots
        $game['gallery'] = $game['screenshots'] ?? [];

        // Simulate reviews
        $reviews = $this->sampleReviews($game['id']);

        return Inertia::render('GameDetail', [
            'game' => $game,
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
}
