<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
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
        $onSale = $request->input('on_sale');
        $comingSoon = $request->input('coming_soon');
        $sort = $request->input('sort');

        // Check if using Eloquent by testing for query builder
        if ($games instanceof \Illuminate\Database\Eloquent\Builder) {
            $games->with('genres', 'platforms');

            if ($search) {
                $games->where('title', 'like', "%{$search}%");
            }
            if ($genre) {
                $games->whereHas('genres', fn ($q) => $q->where('name', $genre));
            }
            if ($platform) {
                $games->whereHas('platforms', fn ($q) => $q->where('slug', $platform));
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
            if ($onSale) {
                $games->where('is_discounted', true);
            }
            if ($comingSoon) {
                $games->where('release_date', '>', now());
            }
            if ($sort === 'newest') {
                $games->orderBy('release_date', 'desc');
            } elseif ($sort === 'price_asc') {
                $games->orderBy('price', 'asc');
            } elseif ($sort === 'price_desc') {
                $games->orderBy('price', 'desc');
            } elseif ($sort === 'name_asc') {
                $games->orderBy('title', 'asc');
            } elseif ($sort === 'name_desc') {
                $games->orderBy('title', 'desc');
            } elseif ($sort === 'rating') {
                $games->orderBy('rating_avg', 'desc');
            }
            $games = $games->get();

            // Normalize genre/platform fields for client-side filtering
            $games = $games->map(function ($game) {
                $g = $game->toArray();
                $g['genre'] = $game->genres->first()?->name ?? $g['genre'] ?? null;
                $g['platforms'] = $game->platforms->pluck('slug')->toArray();
                return $g;
            });
        } else {
            // Filter using collection
            $now = now();
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
            if ($onSale) {
                $games = $games->filter(fn ($g) => $g['is_discounted']);
            }
            if ($comingSoon) {
                $games = $games->filter(fn ($g) => \Carbon\Carbon::parse($g['release_date'])->gt($now));
            }
            if ($sort === 'newest') {
                $games = $games->sortByDesc('release_date');
            } elseif ($sort === 'price_asc') {
                $games = $games->sortBy('price');
            } elseif ($sort === 'price_desc') {
                $games = $games->sortByDesc('price');
            } elseif ($sort === 'name_asc') {
                $games = $games->sortBy('title');
            } elseif ($sort === 'name_desc') {
                $games = $games->sortByDesc('title');
            } elseif ($sort === 'rating') {
                $games = $games->sortByDesc('rating_avg');
            }
        }

        // Main platforms only for the filter sidebar
        $mainPlatformSlugs = ['pc', 'playstation', 'xbox', 'nintendo', 'mac', 'linux'];
        $mainPlatforms = Platform::whereIn('slug', $mainPlatformSlugs)
            ->get(['name', 'slug'])
            ->unique('slug')
            ->map(fn ($p) => ['value' => $p->slug, 'label' => $p->name])
            ->values()
            ->toArray();

        // Compute price range for slider
        $prices = collect($games)->pluck('price')->filter()->map('floatval');
        $minGamePrice = floor($prices->min() ?? 0);
        $maxGamePrice = ceil($prices->max() ?? 100);

        return Inertia::render('Catalog', [
            'games' => $games->values()->toArray(),
            'filters' => $request->only(['search', 'genre', 'platform', 'min_price', 'max_price', 'min_rating', 'on_sale', 'coming_soon', 'sort']),
            'genres' => Genre::all(['name'])->pluck('name')->toArray(),
            'platforms' => $mainPlatforms,
            'priceRange' => ['min' => $minGamePrice, 'max' => $maxGamePrice],
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
