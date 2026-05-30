<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameImage;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed prices for existing games and add demo data for Coming Soon / On Sale.
     */
    public function run(): void
    {
        // 1. Set random prices on ALL games that have price = 0
        $updated = Game::where(function ($q) {
                $q->where('price', 0)->orWhereNull('price');
            })
            ->update([
                'price' => \DB::raw("round((random() * 60 + 5)::numeric, 2)"),
            ]);
        $this->command->info("Updated {$updated} games with random prices.");

        // 2. Mark ~10% of games as discounted (skip previously discounted)
        $discountTarget = Game::where('is_discounted', false)
            ->where('price', '>', 0)
            ->inRandomOrder()
            ->take(100)
            ->get();

        $discountCount = 0;
        foreach ($discountTarget as $game) {
            $discountPct = rand(15, 60);
            $discountPrice = round($game->price * (1 - $discountPct / 100), 2);
            $game->update([
                'is_discounted' => true,
                'discount_price' => $discountPrice,
                'discount_pct' => $discountPct,
            ]);
            $discountCount++;
        }
        $this->command->info("Marked {$discountCount} games as discounted.");

        // 3. Ensure genres, platforms, companies exist for new games
        $genres = ['Action', 'RPG', 'FPS', 'Strategy', 'Sports', 'Simulation', 'Adventure', 'Horror', 'Puzzle', 'Racing'];
        foreach ($genres as $name) {
            Genre::firstOrCreate(['name' => $name, 'slug' => Str::slug($name)]);
        }

        $platformNames = ['windows', 'mac', 'linux', 'playstation', 'xbox', 'nintendo'];
        foreach ($platformNames as $name) {
            Platform::firstOrCreate(['name' => $name, 'slug' => Str::slug($name)]);
        }

        if (Company::count() === 0) {
            Company::factory()->count(5)->create();
        }

        // 4. Create games with FUTURE release dates — Coming Soon section
        $futureGames = [
            [
                'title' => 'Phantom Protocol',
                'description' => 'A stealt h thriller where every shadow hides a secret.',
                'price' => 49.99,
                'release_date' => Carbon::now()->addMonths(2),
                'developer' => 'StealthWorks Games',
                'publisher' => 'Digital Dreams Publishing',
                'genre' => 'Action',
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Phantom+Protocol',
            ],
            [
                'title' => 'Stellar Horizons',
                'description' => 'Colonize alien worlds and build a new civilization among the stars.',
                'price' => 59.99,
                'release_date' => Carbon::now()->addMonths(3),
                'developer' => 'Cosmic Forge',
                'publisher' => 'Stellar Interactive',
                'genre' => 'Sci-Fi',
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Stellar+Horizons',
            ],
            [
                'title' => 'Cursed Relics',
                'description' => 'An archeological adventure uncovering artifacts best left buried.',
                'price' => 39.99,
                'release_date' => Carbon::now()->addWeeks(3),
                'developer' => 'Desert Fox Studios',
                'publisher' => 'Pixel Perfect Games',
                'genre' => 'Adventure',
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Cursed+Relics',
            ],
            [
                'title' => 'Iron Legion',
                'description' => 'Command an army of mechs in a war for global supremacy.',
                'price' => 44.99,
                'release_date' => Carbon::now()->addMonths(1),
                'developer' => 'NeonPixel Games',
                'publisher' => 'Digital Dreams Publishing',
                'genre' => 'Strategy',
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Iron+Legion',
            ],
        ];

        foreach ($futureGames as $data) {
            $slug = Str::slug($data['title']);
            if (Game::where('slug', $slug)->exists()) {
                continue;
            }

            $game = Game::create([
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'about' => $data['description'] . ' Coming soon to Steamish.',
                'price' => $data['price'],
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => $data['release_date'],
                'developer' => $data['developer'],
                'publisher' => $data['publisher'],
                'genre' => $data['genre'],
                'platforms' => json_encode(['windows', 'playstation', 'xbox']),
                'cover' => $data['cover'],
                'header' => str_replace('300x400', '1200x400', str_replace('2a475e', '171a21', $data['cover'])),
                'rating_avg' => 0,
                'rating_count' => 0,
            ]);

            // Attach genres and platforms
            $genre = Genre::where('name', $data['genre'])->first();
            if ($genre) {
                $game->genres()->attach($genre);
            }
            $platforms = Platform::whereIn('name', ['windows', 'playstation', 'xbox'])->get();
            $game->platforms()->attach($platforms);

            GameImage::create([
                'game_id' => $game->id,
                'url' => 'https://placehold.co/800x450/2a475e/1a9fff?text=' . urlencode($data['title']) . '+Screenshot',
                'type' => 'screenshot',
                'sort_order' => 0,
            ]);

            $this->command->info("  Created future game: {$data['title']} (releases {$data['release_date']->format('Y-m-d')})");
        }

        // 5. Create deeply discounted games — On Sale section
        $onSaleGames = [
            [
                'title' => 'Budget Cuts',
                'description' => 'Survive corporate layoffs in this satirical office simulator.',
                'price' => 19.99,
                'discount_pct' => 60,
                'developer' => 'Cubicle Games',
                'publisher' => 'Simulation Masters',
                'genre' => 'Simulation',
            ],
            [
                'title' => 'Pixel Racer',
                'description' => 'Retro-style arcade racing with modern physics.',
                'price' => 14.99,
                'discount_pct' => 40,
                'developer' => 'RetroForge',
                'publisher' => 'Pixel Perfect Games',
                'genre' => 'Sports',
            ],
            [
                'title' => 'Dungeon Dashers',
                'description' => 'Fast-paced roguelike dungeon crawling for up to 4 players.',
                'price' => 24.99,
                'discount_pct' => 50,
                'developer' => 'Mythic Studios',
                'publisher' => 'Fantasy World Entertainment',
                'genre' => 'RPG',
            ],
        ];

        foreach ($onSaleGames as $data) {
            $slug = Str::slug($data['title']);
            if (Game::where('slug', $slug)->exists()) {
                continue;
            }

            $discountPrice = round($data['price'] * (1 - $data['discount_pct'] / 100), 2);

            $game = Game::create([
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'about' => $data['description'] . ' Now on sale at Steamish!',
                'price' => $data['price'],
                'discount_price' => $discountPrice,
                'discount_pct' => $data['discount_pct'],
                'is_discounted' => true,
                'release_date' => Carbon::now()->subMonths(rand(3, 12)),
                'developer' => $data['developer'],
                'publisher' => $data['publisher'],
                'genre' => $data['genre'],
                'platforms' => json_encode(['windows', 'mac']),
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=' . urlencode($data['title']),
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=' . urlencode($data['title']),
                'rating_avg' => round(rand(35, 48) / 10, 1),
                'rating_count' => rand(100, 3000),
            ]);

            $genre = Genre::where('name', $data['genre'])->first();
            if ($genre) {
                $game->genres()->attach($genre);
            }
            $platforms = Platform::whereIn('name', ['windows', 'mac'])->get();
            $game->platforms()->attach($platforms);

            GameImage::create([
                'game_id' => $game->id,
                'url' => 'https://placehold.co/800x450/2a475e/1a9fff?text=' . urlencode($data['title']) . '+Screenshot',
                'type' => 'screenshot',
                'sort_order' => 0,
            ]);

            $this->command->info("  Created on-sale game: {$data['title']} (was \${$data['price']}, now \${$discountPrice})");
        }

        // Summary
        $totalGames = Game::count();
        $discounted = Game::where('is_discounted', true)->count();
        $future = Game::where('release_date', '>', Carbon::now())->count();
        $hasPrice = Game::where('price', '>', 0)->count();

        $this->command->info("--- Demo data complete ---");
        $this->command->info("Total games: {$totalGames}");
        $this->command->info("With price > 0: {$hasPrice}");
        $this->command->info("Discounted: {$discounted}");
        $this->command->info("Future releases: {$future}");
    }
}
