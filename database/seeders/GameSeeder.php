<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Game;
use App\Models\GameImage;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create demo user
        $demoUser = User::create([
            'name' => 'Demo Player',
            'username' => 'demo_player',
            'email' => 'demo@steamish.test',
            'password' => Hash::make('password'),
            'timezone' => 'UTC',
        ]);

        // 2. Create 5-8 additional users for reviews
        $generatedUsers = User::factory()->count(7)->create();
        $allUsers = collect([$demoUser])->merge($generatedUsers);

        // 3. Ensure genres, platforms, and companies exist for the factory
        Genre::factory()->count(8)->create();
        Platform::factory()->count(6)->create();
        Company::factory()->count(5)->create();

        // 4. Create sample games via factory (attaches genres/platforms via afterCreating)
        $games = Game::factory()->count(10)->create();

        // Create a screenshot for each game
        foreach ($games as $game) {
            GameImage::create([
                'game_id' => $game->id,
                'url' => 'https://placehold.co/800x450/2a475e/1a9fff?text=' . urlencode($game->title) . '+Screenshot',
                'type' => 'screenshot',
                'sort_order' => 0,
            ]);
        }

        // 5. Create reviews
        $allGames = Game::all();
        $reviewBodies = [
            'Absolutely incredible game! The graphics are stunning and the gameplay is incredibly smooth. I have sunk over 100 hours and I am still finding new things to explore.',
            'Really solid game with great mechanics. The story is engaging and keeps you hooked. Would give it 5 stars if the multiplayer had better matchmaking.',
            'Decent game but feels a bit unpolished in places. Some bugs that should have been caught in QA. The core gameplay loop is fun though.',
            'Game of the year material right here. Everything from the soundtrack to the level design is top notch. Cannot recommend this enough.',
            'Great game for both casual and hardcore players. The difficulty curve is well balanced and there is plenty of content for the price.',
            'One of the best gaming experiences I have had in years. The attention to detail is incredible and the world feels truly alive.',
            'A masterpiece in game design. Every system works together perfectly to create an addictive and rewarding gameplay loop.',
            'Solid entry in the franchise. It does not reinvent the wheel but it polishes the existing formula to near perfection.',
            'The gameplay is fun but the story fell a bit flat for me. Still worth playing for the mechanics alone.',
            'Absolutely worth every penny. The amount of content here is staggering and they are still adding more for free.',
        ];

        $reviewCount = 0;
        foreach ($allGames as $game) {
            // Skip reviews for unreleased games
            if ($game->release_date > now()) {
                continue;
            }

            // Each game gets 2-5 reviews
            $numReviews = rand(2, 5);
            $selectedUsers = $allUsers->random(min($numReviews, $allUsers->count()));

            foreach ($selectedUsers as $user) {
                $hoursPlayed = rand(5, 200);
                $rating = rand(3, 5);

                Review::create([
                    'user_id' => $user->id,
                    'game_id' => $game->id,
                    'rating' => $rating,
                    'body' => $reviewBodies[array_rand($reviewBodies)],
                    'hours_played' => $hoursPlayed,
                    'is_recommended' => $rating >= 3,
                ]);
                $reviewCount++;
            }
        }

        // 6. Update games with accurate rating_avg and rating_count
        foreach ($allGames as $game) {
            $reviews = Review::where('game_id', $game->id)->get();
            if ($reviews->count() > 0) {
                $game->update([
                    'rating_avg' => $reviews->avg('rating'),
                    'rating_count' => $reviews->count(),
                ]);
            }
        }

        // 7. Create purchases for demo user
        $gamesForPurchase = $allGames->filter(fn ($g) => $g->release_date <= now())->random(min(12, $allGames->count()));
        $purchaseCount = 0;

        foreach ($gamesForPurchase as $game) {
            $price = $game->is_discounted && $game->discount_price
                ? $game->discount_price
                : $game->price;

            Purchase::create([
                'user_id' => $demoUser->id,
                'game_id' => $game->id,
                'stripe_session_id' => 'cs_seed_' . uniqid(),
                'amount_paid' => $price,
            ]);
            $purchaseCount++;
        }

        $this->command->info("Seeded: {$allGames->count()} games, {$reviewCount} reviews, {$purchaseCount} purchases, {$allUsers->count()} users");
    }
}
