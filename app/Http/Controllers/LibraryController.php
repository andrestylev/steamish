<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    use HasGameData;

    /**
     * Show the user's game library.
     */
    public function index(): Response
    {
        $purchasedGameIds = Purchase::where('user_id', Auth::id())
            ->pluck('game_id')
            ->unique()
            ->toArray();

        if (empty($purchasedGameIds)) {
            return Inertia::render('Library', [
                'games' => [],
                'totalGames' => 0,
            ]);
        }

        // Try DB games first
        $dbGames = Game::whereIn('id', $purchasedGameIds)->get();
        if ($dbGames->isNotEmpty()) {
            $games = $dbGames->map(function ($game) {
                return [
                    'id' => $game->id,
                    'title' => $game->title,
                    'slug' => $game->slug,
                    'cover' => $game->cover,
                    'header' => $game->header,
                    'genre' => $game->genres->first()?->name ?? $game->genre,
                    'developer' => $game->developer,
                    'price' => $game->price,
                    'is_discounted' => $game->is_discounted,
                    'discount_price' => $game->discount_price,
                    'discount_pct' => $game->discount_pct,
                    'rating_avg' => $game->rating_avg,
                    'rating_count' => $game->rating_count,
                    'platforms' => $game->platforms->pluck('slug')->toArray(),
                    'release_date' => $game->release_date,
                    'about' => $game->about,
                ];
            })->values()->toArray();

            return Inertia::render('Library', [
                'games' => $games,
                'totalGames' => count($games),
            ]);
        }

        // Fallback to hardcoded data
        $allGames = $this->allGames();
        $games = collect($allGames)->filter(function ($game) use ($purchasedGameIds) {
            return in_array($game['id'], $purchasedGameIds);
        })->values()->all();

        return Inertia::render('Library', [
            'games' => $games,
            'totalGames' => count($games),
        ]);
    }
}
