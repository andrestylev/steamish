<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    use HasGameData;

    /**
     * Show the game catalog with search and filters.
     */
    public function index(Request $request): Response
    {
        $games = $this->getGames();

        // Apply filters
        $search = $request->input('search');
        $genre = $request->input('genre');
        $platform = $request->input('platform');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $minRating = $request->input('min_rating');

        // Check if using Eloquent by testing for query builder
        if ($games instanceof \Illuminate\Database\Eloquent\Builder) {
            if ($search) {
                $games->where('title', 'like', "%{$search}%");
            }
            if ($genre) {
                $games->where('genre', $genre);
            }
            if ($platform) {
                $games->whereJsonContains('platforms', $platform);
            }
            if ($minPrice !== null) {
                $games->where('price', '>=', (float) $minPrice);
            }
            if ($maxPrice !== null) {
                $games->where('price', '<=', (float) $maxPrice);
            }
            if ($minRating) {
                $games->where('rating_avg', '>=', (float) $minRating);
            }
            $games = $games->get();
        } else {
            // Filter using collection
            if ($search) {
                $games = $games->filter(fn ($g) => stripos($g['title'], $search) !== false);
            }
            if ($genre) {
                $games = $games->filter(fn ($g) => ($g['genre'] ?? '') === $genre);
            }
            if ($platform) {
                $games = $games->filter(fn ($g) => in_array($platform, $g['platforms'] ?? []));
            }
            if ($minPrice !== null) {
                $games = $games->filter(fn ($g) => (float) $g['price'] >= (float) $minPrice);
            }
            if ($maxPrice !== null) {
                $games = $games->filter(fn ($g) => (float) $g['price'] <= (float) $maxPrice);
            }
            if ($minRating) {
                $games = $games->filter(fn ($g) => (float) $g['rating_avg'] >= (float) $minRating);
            }
        }

        return Inertia::render('Catalog', [
            'games' => $games->values()->toArray(),
            'filters' => $request->only(['search', 'genre', 'platform', 'min_price', 'max_price', 'min_rating']),
            'genres' => [
                'Action',
                'RPG',
                'FPS',
                'Simulation',
                'Strategy',
                'Sports',
                'Horror',
                'Adventure',
                'Sandbox',
                'Battle Royale',
            ],
            'platforms' => [
                ['value' => 'windows', 'label' => 'Windows'],
                ['value' => 'mac', 'label' => 'Mac'],
                ['value' => 'linux', 'label' => 'Linux'],
                ['value' => 'playstation', 'label' => 'PlayStation'],
                ['value' => 'xbox', 'label' => 'Xbox'],
                ['value' => 'nintendo', 'label' => 'Nintendo'],
            ],
            'priceRanges' => [
                ['label' => 'Under $10', 'min' => 0, 'max' => 10],
                ['label' => '$10 - $30', 'min' => 10, 'max' => 30],
                ['label' => '$30 - $50', 'min' => 30, 'max' => 50],
                ['label' => '$50+', 'min' => 50, 'max' => null],
            ],
            'ratings' => [4, 3, 2, 1],
        ]);
    }

    /**
     * Get games from Eloquent if available, otherwise from HasGameData trait.
     *
     * @return \Illuminate\Database\Eloquent\Builder|Collection
     */
    private function getGames()
    {
        if (Game::count() > 0) {
            return Game::query();
        }

        return collect($this->allGames());
    }
}
