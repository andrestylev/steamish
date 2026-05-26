<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    use HasGameData;

    /**
     * Show the home page with hero carousel and game sections.
     */
    public function index(): Response
    {
        $games = $this->getGames();
        $today = Carbon::today();

        // Compute coming_soon for each game
        $games = $games->map(function ($g) use ($today) {
            $game = is_object($g) ? $g->toArray() : (array) $g;
            $releaseDate = isset($game['release_date']) ? Carbon::parse($game['release_date']) : $today;
            $game['coming_soon'] = $releaseDate->gt($today);
            return $game;
        });

        return Inertia::render('Home', [
            'featuredGames' => $games->take(5)->values()->toArray(),
            'newReleases' => $games->sortByDesc('release_date')->take(12)->values()->toArray(),
            'topRated' => $games->sortByDesc('rating_avg')->take(12)->values()->toArray(),
            'comingSoon' => $games->filter(fn ($g) => $g['coming_soon'])->take(12)->values()->toArray(),
            'onSale' => $games->filter(fn ($g) => $g['is_discounted'])->take(12)->values()->toArray(),
        ]);
    }

    /**
     * Get games from Eloquent if available, otherwise from HasGameData trait.
     */
    private function getGames(): Collection
    {
        if (Game::count() > 0) {
            return Game::with('images')->get();
        }

        return collect($this->allGames());
    }
}
