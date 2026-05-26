<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserReportController extends Controller
{
    use HasGameData;

    /**
     * Show the user's personal stats with playtime chart.
     */
    public function index(): Response
    {
        // Top 5 games by hours_played (from reviews)
        $topGames = Review::where('user_id', Auth::id())
            ->where('hours_played', '>', 0)
            ->orderByDesc('hours_played')
            ->take(5)
            ->with('game')
            ->get();

        $hasDbData = Game::count() > 0;

        if ($hasDbData) {
            // Use Eloquent relations
            $items = $topGames->map(function ($review) {
                $game = $review->game;
                return [
                    'title' => $game?->title ?? 'Unknown Game',
                    'cover' => $game?->cover,
                    'slug' => $game?->slug,
                    'hours_played' => $review->hours_played,
                    'genre' => $game?->genre,
                ];
            })->values();
        } else {
            // Fallback: merge with hardcoded game data for display
            $allGames = $this->allGames();
            $items = $topGames->map(function ($review) use ($allGames) {
                $gameData = collect($allGames)->firstWhere('id', $review->game_id);
                return [
                    'title' => $gameData['title'] ?? 'Unknown Game',
                    'cover' => $gameData['cover'] ?? null,
                    'slug' => $gameData['slug'] ?? null,
                    'hours_played' => $review->hours_played,
                    'genre' => $gameData['genre'] ?? null,
                ];
            })->values();
        }

        $hasPlaytime = $items->isNotEmpty();

        return Inertia::render('User/Stats', [
            'items' => $items,
            'hasPlaytime' => $hasPlaytime,
        ]);
    }
}
