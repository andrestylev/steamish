<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameImage;
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

        // 3. Create games with realistic data
        $games = $this->getGameData();

        foreach ($games as $gameData) {
            $screenshots = $gameData['screenshots'] ?? [];
            unset($gameData['screenshots']);

            $game = Game::create($gameData);

            // Create game images (screenshots)
            foreach ($screenshots as $index => $url) {
                GameImage::create([
                    'game_id' => $game->id,
                    'url' => $url,
                    'type' => 'screenshot',
                    'sort_order' => $index,
                ]);
            }
        }

        // 4. Create 50+ reviews
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

        // 5. Update games with accurate rating_avg and rating_count
        foreach ($allGames as $game) {
            $reviews = Review::where('game_id', $game->id)->get();
            if ($reviews->count() > 0) {
                $game->update([
                    'rating_avg' => $reviews->avg('rating'),
                    'rating_count' => $reviews->count(),
                ]);
            }
        }

        // 6. Create 10+ purchases for demo user with playtime in reviews
        $gamesForPurchase = $allGames->filter(fn ($g) => $g->release_date <= now())->random(12);
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

    /**
     * Get realistic game data.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getGameData(): array
    {
        $now = now();
        $future = now()->addMonths(6);

        return [
            // Required games from the spec
            [
                'title' => 'Grand Theft Auto V',
                'slug' => 'grand-theft-auto-v',
                'description' => 'Los Santos: a sprawling sun-soaked metropolis full of self-help gurus, starlets and fading celebrities.',
                'about' => 'When a young street hustler, a retired bank robber and a terrifying psychopath find themselves entangled with some of the most frightening and deranged elements of the criminal underworld, the U.S. government and the entertainment industry, they must pull off a series of dangerous heists to survive in a ruthless city.',
                'price' => 39.99,
                'discount_price' => 29.99,
                'discount_pct' => 25,
                'is_discounted' => true,
                'release_date' => '2015-04-14',
                'developer' => 'Rockstar North',
                'publisher' => 'Rockstar Games',
                'genre' => 'Action',
                'platforms' => ['windows', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=GTA+V',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Grand+Theft+Auto+V',
                'rating_avg' => 4.8,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-3470 / AMD X8 FX-8350 | RAM: 8 GB | GPU: NVIDIA GTX 660 2GB / AMD HD7870 2GB | Storage: 150 GB SSD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-2700K / AMD Ryzen 5 2600 | RAM: 16 GB | GPU: NVIDIA GTX 1060 6GB / AMD RX 580 8GB | Storage: 150 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=GTA+V+Screenshot+1',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=GTA+V+Screenshot+2',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=GTA+V+Screenshot+3',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=GTA+V+Screenshot+4',
                ],
            ],
            [
                'title' => 'Grand Theft Auto VI',
                'slug' => 'grand-theft-auto-vi',
                'description' => 'Welcome to the state of Leonida, home to the biggest, most insane and most hilarious entertainment concepts in the world.',
                'about' => 'Grand Theft Auto VI heads back to the state of Leonida, home to the neon-soaked streets of Vice City and beyond in the biggest, most immersive evolution of the Grand Theft Auto series yet. Join Lucia, the first female protagonist in a GTA game in over 20 years.',
                'price' => 69.99,
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => $future->toDateString(),
                'developer' => 'Rockstar North',
                'publisher' => 'Rockstar Games',
                'genre' => 'Action',
                'platforms' => ['windows', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=GTA+VI',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Grand+Theft+Auto+VI+-+Coming+Soon',
                'rating_avg' => 0,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-8700K / AMD Ryzen 7 3700X | RAM: 16 GB | GPU: NVIDIA RTX 2070 8GB / AMD RX 5700 XT 8GB | Storage: 300 GB SSD',
                'rec_req' => 'OS: Windows 11 64-bit | CPU: Intel Core i9-10900K / AMD Ryzen 9 5900X | RAM: 32 GB | GPU: NVIDIA RTX 3080 12GB / AMD RX 6800 XT 16GB | Storage: 300 GB NVMe SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=GTA+VI+Teaser+1',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=GTA+VI+Teaser+2',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=GTA+VI+Teaser+3',
                ],
            ],
            [
                'title' => 'Cyberpunk 2077',
                'slug' => 'cyberpunk-2077',
                'description' => 'Cyberpunk 2077 is an open-world, action-adventure RPG set in the megalopolis of Night City.',
                'about' => 'Cyberpunk 2077 is an open-world, action-adventure RPG set in Night City — a megalopolis obsessed with power, glamour and body modification. You play as V, a mercenary outlaw going after a one-of-a-kind implant that is the key to immortality. Following Update 2.0 and Phantom Liberty, Cyberpunk 2077 now lives up to its full promise.',
                'price' => 59.99,
                'discount_price' => 29.99,
                'discount_pct' => 50,
                'is_discounted' => true,
                'release_date' => '2020-12-10',
                'developer' => 'CD Projekt Red',
                'publisher' => 'CD Projekt',
                'genre' => 'RPG',
                'platforms' => ['windows', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Cyberpunk+2077',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Cyberpunk+2077',
                'rating_avg' => 4.5,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-3570K / AMD FX-8350 | RAM: 8 GB | GPU: NVIDIA GTX 970 4GB / AMD R9 390 4GB | Storage: 70 GB SSD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-4790 / AMD Ryzen 5 3600 | RAM: 12 GB | GPU: NVIDIA RTX 2060 6GB / AMD RX 5700 XT 8GB | Storage: 70 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Cyberpunk+Screenshot+1',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Cyberpunk+Screenshot+2',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Cyberpunk+Screenshot+3',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Cyberpunk+Screenshot+4',
                ],
            ],
            [
                'title' => 'Elden Ring',
                'slug' => 'elden-ring',
                'description' => 'A new fantasy action RPG from FromSoftware, creators of the Dark Souls series and Bloodborne.',
                'about' => 'THE NEW FANTASY ACTION RPG. Rise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between. A vast world where open fields with a variety of situations and huge dungeons with complex and three-dimensional designs are seamlessly connected.',
                'price' => 59.99,
                'discount_price' => 41.99,
                'discount_pct' => 30,
                'is_discounted' => true,
                'release_date' => '2022-02-25',
                'developer' => 'FromSoftware',
                'publisher' => 'Bandai Namco Entertainment',
                'genre' => 'RPG',
                'platforms' => ['windows', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Elden+Ring',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Elden+Ring',
                'rating_avg' => 4.7,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-8400 / AMD Ryzen 3 3300X | RAM: 12 GB | GPU: NVIDIA GTX 1060 3GB / AMD RX 580 4GB | Storage: 60 GB HDD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-8700K / AMD Ryzen 5 3600X | RAM: 16 GB | GPU: NVIDIA GTX 1070 8GB / AMD RX Vega 56 8GB | Storage: 60 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Elden+Ring+Screenshot+1',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Elden+Ring+Screenshot+2',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Elden+Ring+Screenshot+3',
                ],
            ],
            [
                'title' => 'Counter-Strike 2',
                'slug' => 'counter-strike-2',
                'description' => 'For over two decades, Counter-Strike has offered an elite competitive experience.',
                'about' => 'Counter-Strike 2 is the largest technical leap forward in Counter-Strike history, ensuring new features and updates for years to come. All of the game mode features you know and love are here, rebuilt from the ground up using Source 2. Free to play for everyone.',
                'price' => 0.00,
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => '2023-09-27',
                'developer' => 'Valve',
                'publisher' => 'Valve',
                'genre' => 'FPS',
                'platforms' => ['windows', 'mac', 'linux'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=CS2',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Counter-Strike+2',
                'rating_avg' => 4.0,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-7500 / AMD Ryzen 5 1600 | RAM: 8 GB | GPU: NVIDIA GTX 1050 Ti / AMD RX 570 | Storage: 85 GB SSD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-11700K / AMD Ryzen 7 5800X | RAM: 16 GB | GPU: NVIDIA RTX 3060 / AMD RX 6700 XT | Storage: 85 GB NVMe SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=CS2+Mirage',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=CS2+Inferno',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=CS2+Dust2',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=CS2+Nuke',
                ],
            ],
            [
                'title' => 'Minecraft',
                'slug' => 'minecraft',
                'description' => 'Create, explore, survive. Build anything you can imagine in the infinite world of Minecraft.',
                'about' => 'Minecraft is a game about placing blocks and adventures. Explore randomly generated worlds and build amazing things from the simplest of homes to the grandest of castles. Play in Creative Mode with unlimited resources or mine deep into the world in Survival Mode, crafting weapons and armor to fend off dangerous mobs.',
                'price' => 29.99,
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => '2011-11-18',
                'developer' => 'Mojang Studios',
                'publisher' => 'Xbox Game Studios',
                'genre' => 'Sandbox',
                'platforms' => ['windows', 'mac', 'linux', 'playstation', 'xbox', 'nintendo'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Minecraft',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Minecraft',
                'rating_avg' => 4.8,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 | CPU: Intel Core i3-3210 / AMD A8-7600 | RAM: 4 GB | GPU: NVIDIA GeForce 400 Series / AMD Radeon HD 7000 series | Storage: 180 MB',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-4690 / AMD A10-7800 | RAM: 8 GB | GPU: NVIDIA GeForce 700 Series / AMD Radeon Rx 200 Series | Storage: 4 GB',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Minecraft+Survival',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Minecraft+Creative',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Minecraft+Redstone',
                ],
            ],
            [
                'title' => 'Red Dead Redemption 2',
                'slug' => 'red-dead-redemption-2',
                'description' => 'America, 1899. Arthur Morgan and the Van der Linde gang are outlaws on the run.',
                'about' => 'America, 1899. Arthur Morgan and the Van der Linde gang are outlaws on the run. With federal agents and the best bounty hunters in the nation massing on their heels, the gang must rob, steal and fight their way across the rugged heartland of America in order to survive. RDR2 is an epic tale of life in America at the dawn of the modern age.',
                'price' => 59.99,
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => '2019-11-05',
                'developer' => 'Rockstar Studios',
                'publisher' => 'Rockstar Games',
                'genre' => 'Action',
                'platforms' => ['windows', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=RDR2',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Red+Dead+Redemption+2',
                'rating_avg' => 4.9,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-2500K / AMD FX-6300 | RAM: 8 GB | GPU: NVIDIA GTX 1050 2GB / AMD RX 550 2GB | Storage: 150 GB HDD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-4770K / AMD Ryzen 5 1500X | RAM: 12 GB | GPU: NVIDIA GTX 1060 6GB / AMD RX 480 4GB | Storage: 150 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=RDR2+Landscape+1',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=RDR2+Landscape+2',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=RDR2+Action',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=RDR2+Hunting',
                ],
            ],
            [
                'title' => 'The Witcher 3: Wild Hunt',
                'slug' => 'the-witcher-3-wild-hunt',
                'description' => 'You are Geralt of Rivia, mercenary monster slayer. Embark on the most important contract of your life.',
                'about' => 'The Witcher 3: Wild Hunt is a story-driven, next-generation open world role-playing game set in a visually stunning fantasy universe full of meaningful choices and impactful consequences. In The Witcher, you play as professional monster hunter Geralt of Rivia tasked with finding a child of prophecy in a vast open world rich with merchant cities, Viking pirate islands, dangerous mountain passes, and forgotten caverns to explore.',
                'price' => 39.99,
                'discount_price' => 9.99,
                'discount_pct' => 75,
                'is_discounted' => true,
                'release_date' => '2015-05-19',
                'developer' => 'CD Projekt Red',
                'publisher' => 'CD Projekt',
                'genre' => 'RPG',
                'platforms' => ['windows', 'mac', 'playstation', 'xbox', 'nintendo'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=The+Witcher+3',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=The+Witcher+3+-+Wild+Hunt',
                'rating_avg' => 4.8,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-2500K / AMD Phenom II X4 940 | RAM: 6 GB | GPU: NVIDIA GTX 660 / AMD Radeon HD 7870 | Storage: 35 GB',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-3770 / AMD AMD FX-8350 | RAM: 8 GB | GPU: NVIDIA GTX 770 / AMD Radeon R9 290 | Storage: 35 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Witcher+3+Landscape',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Witcher+3+Combat',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Witcher+3+Story',
                ],
            ],
            [
                'title' => 'God of War',
                'slug' => 'god-of-war',
                'description' => 'Kratos is back. This time, he fights alongside his son in the brutal Norse wilds.',
                'about' => 'His vengeance against the Gods of Olympus years behind him, Kratos now lives as a man in the realm of Norse Gods and monsters. It is in this harsh, unforgiving world that he must fight to survive... and teach his son to do the same. Bold new beginnings — a second chance. Enter the Norse realm with a new over-the-shoulder free camera that brings the player closer to the action.',
                'price' => 49.99,
                'discount_price' => 24.99,
                'discount_pct' => 50,
                'is_discounted' => true,
                'release_date' => '2022-01-14',
                'developer' => 'Santa Monica Studio',
                'publisher' => 'Sony Interactive Entertainment',
                'genre' => 'Action',
                'platforms' => ['windows', 'playstation'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=God+of+War',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=God+of+War+%282018%29',
                'rating_avg' => 4.7,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel i5-2500k / AMD Ryzen 3 1200 | RAM: 8 GB | GPU: NVIDIA GTX 960 / AMD R9 290x | Storage: 70 GB HDD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel i5-6600k / AMD Ryzen 5 2400G | RAM: 16 GB | GPU: NVIDIA GTX 1060 / AMD RX 580 | Storage: 70 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=God+of+War+Norse',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=God+of+War+Atreus',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=God+of+War+Combat',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=God+of+War+Boss',
                ],
            ],
            [
                'title' => 'Hogwarts Legacy',
                'slug' => 'hogwarts-legacy',
                'description' => 'Experience Hogwarts in the 1800s. Your character is a student holding the key to an ancient secret.',
                'about' => 'Hogwarts Legacy is an immersive, open-world action RPG set in the world first introduced in the Harry Potter books. For the first time, experience Hogwarts in the 1800s. Your character is a student who holds the key to an ancient secret that threatens to tear the wizarding world apart.',
                'price' => 59.99,
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => '2023-02-10',
                'developer' => 'Avalanche Software',
                'publisher' => 'Warner Bros. Games',
                'genre' => 'RPG',
                'platforms' => ['windows', 'playstation', 'xbox', 'nintendo'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Hogwarts+Legacy',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Hogwarts+Legacy',
                'rating_avg' => 4.3,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-6600 / AMD Ryzen 5 1400 | RAM: 16 GB | GPU: NVIDIA GTX 960 4GB / AMD RX 470 4GB | Storage: 85 GB HDD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-8700 / AMD Ryzen 5 3600 | RAM: 16 GB | GPU: NVIDIA RTX 2060 / AMD RX 5700 XT | Storage: 85 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Hogwarts+Castle',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Hogwarts+Flying',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Hogwarts+Combat',
                ],
            ],
            [
                'title' => 'Baldur\'s Gate 3',
                'slug' => 'baldurs-gate-3',
                'description' => 'The next generation of RPG from the creators of Divinity: Original Sin 2.',
                'about' => 'Baldur\'s Gate 3 is a story-rich, party-based RPG set in the universe of Dungeons & Dragons, where your choices shape a tale of fellowship and betrayal, survival and sacrifice, and the lure of absolute power. Baldur\'s Gate 3 won Game of the Year at The Game Awards 2023.',
                'price' => 59.99,
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => '2023-08-03',
                'developer' => 'Larian Studios',
                'publisher' => 'Larian Studios',
                'genre' => 'RPG',
                'platforms' => ['windows', 'mac', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Baldurs+Gate+3',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Baldur%27s+Gate+3',
                'rating_avg' => 4.9,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel i5-4690 / AMD FX 8350 | RAM: 8 GB | GPU: NVIDIA GTX 970 / AMD RX 480 | Storage: 150 GB SSD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel i7-8700K / AMD Ryzen 5 3600 | RAM: 16 GB | GPU: NVIDIA RTX 2060 / AMD RX 5700 XT | Storage: 150 GB NVMe SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=BG3+Party',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=BG3+Combat',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=BG3+Dialogue',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=BG3+Environment',
                ],
            ],
            [
                'title' => 'EA SPORTS FC 25',
                'slug' => 'ea-sports-fc-25',
                'description' => 'Welcome to the world of football. Play with the biggest names in world football.',
                'about' => 'EA SPORTS FC 25 brings you closer to The World\'s Game with new ways to play, more game-changing realism, and the most authentic experience in football gaming. Featuring over 19,000+ players, 700+ teams, 100+ stadiums, and over 30+ leagues worldwide.',
                'price' => 69.99,
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => '2024-09-27',
                'developer' => 'EA Sports',
                'publisher' => 'Electronic Arts',
                'genre' => 'Sports',
                'platforms' => ['windows', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=EA+FC+25',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=EA+SPORTS+FC+25',
                'rating_avg' => 4.0,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-6600k / AMD Ryzen 5 1600 | RAM: 8 GB | GPU: NVIDIA GTX 1050 Ti / AMD RX 560 | Storage: 100 GB SSD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-8700 / AMD Ryzen 7 3700X | RAM: 12 GB | GPU: NVIDIA RTX 2060 / AMD RX 5700 | Storage: 100 GB NVMe SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=FC25+Match',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=FC25+Celebration',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=FC25+Ultimate+Team',
                ],
            ],
            [
                'title' => 'Call of Duty: Modern Warfare III',
                'slug' => 'call-of-duty-modern-warfare-iii',
                'description' => 'The ultimate threat returns in Call of Duty: Modern Warfare III.',
                'about' => 'In the direct sequel to the record-breaking Call of Duty: Modern Warfare II, Captain Price and Task Force 141 face off against the ultimate threat. The ultranationalist war criminal Vladimir Makarov is extending his grasp across the world, causing Task Force 141 to fight like never before.',
                'price' => 69.99,
                'discount_price' => 48.99,
                'discount_pct' => 30,
                'is_discounted' => true,
                'release_date' => '2023-11-10',
                'developer' => 'Sledgehammer Games',
                'publisher' => 'Activision',
                'genre' => 'FPS',
                'platforms' => ['windows', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=MW3',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Call+of+Duty+Modern+Warfare+III',
                'rating_avg' => 4.1,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-6600 / AMD Ryzen 5 1400 | RAM: 16 GB | GPU: NVIDIA GTX 1060 / AMD RX 580 | Storage: 149 GB SSD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-8700K / AMD Ryzen 7 1800X | RAM: 16 GB | GPU: NVIDIA RTX 3060 / AMD RX 6600 XT | Storage: 149 GB NVMe SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=MW3+Multiplayer',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=MW3+Campaign',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=MW3+Zombies',
                ],
            ],
            [
                'title' => 'Fortnite',
                'slug' => 'fortnite',
                'description' => 'Drop into the Battle Royale. Build, fight, and survive to be the last one standing.',
                'about' => 'Fortnite is the free, always evolving, multiplayer game where you and your friends battle to be the last one standing or collaborate to create your dream Fortnite world. Play both Battle Royale and Fortnite Creative for free. Download now and jump into the action. Free to play!',
                'price' => 0.00,
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => '2017-07-21',
                'developer' => 'Epic Games',
                'publisher' => 'Epic Games',
                'genre' => 'Battle Royale',
                'platforms' => ['windows', 'mac', 'playstation', 'xbox', 'nintendo'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Fortnite',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Fortnite',
                'rating_avg' => 4.2,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-2300 / AMD FX-4350 | RAM: 8 GB | GPU: NVIDIA GTX 660 / AMD HD 7870 | Storage: 80 GB',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-8700 / AMD Ryzen 7 2700 | RAM: 16 GB | GPU: NVIDIA RTX 2060 / AMD RX 5700 | Storage: 80 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Fortnite+Battle',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Fortnite+Building',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Fortnite+Creative',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Fortnite+Event',
                ],
            ],
            [
                'title' => 'Valorant',
                'slug' => 'valorant',
                'description' => 'Tactical 5v5 character-based shooter from Riot Games. Defy the limits.',
                'about' => 'Valorant is a free-to-play first-person tactical hero shooter developed and published by Riot Games. A competitive 5v5 character-based tactical shooter, Valorant features an international cast of playable characters called "Agents," each with unique abilities that allow for tactical, team-based play. Free to play!',
                'price' => 0.00,
                'discount_price' => null,
                'discount_pct' => null,
                'is_discounted' => false,
                'release_date' => '2020-06-02',
                'developer' => 'Riot Games',
                'publisher' => 'Riot Games',
                'genre' => 'FPS',
                'platforms' => ['windows'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Valorant',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Valorant',
                'rating_avg' => 4.4,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i3-4150 / AMD Ryzen 3 1200 | RAM: 4 GB | GPU: NVIDIA GT 730 | Storage: 80 GB',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-9400F / AMD Ryzen 5 2600X | RAM: 8 GB | GPU: NVIDIA GTX 1050 Ti | Storage: 80 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Valorant+Agents',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Valorant+Map',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Valorant+Combat',
                ],
            ],
            // Additional games to reach 20+
            [
                'title' => 'Stardew Valley',
                'slug' => 'stardew-valley',
                'description' => 'You inherited your grandfather\'s old farm plot. Begin your new life.',
                'about' => 'You\'ve inherited your grandfather\'s old farm plot in Stardew Valley. Armed with hand-me-down tools and a few coins, you set out to begin your new life. Can you learn to live off the land and turn these overgrown fields into a thriving home? It won\'t be easy.',
                'price' => 14.99,
                'discount_price' => 9.99,
                'discount_pct' => 33,
                'is_discounted' => true,
                'release_date' => '2016-02-26',
                'developer' => 'ConcernedApe',
                'publisher' => 'ConcernedApe',
                'genre' => 'Simulation',
                'platforms' => ['windows', 'mac', 'linux', 'playstation', 'xbox', 'nintendo'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Stardew+Valley',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Stardew+Valley',
                'rating_avg' => 4.9,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 7+ | CPU: 2 GHz | RAM: 2 GB | GPU: 256 MB video memory | Storage: 500 MB',
                'rec_req' => 'OS: Windows 10 | CPU: Any 2+ GHz | RAM: 4 GB | GPU: Any | Storage: 500 MB',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Stardew+Farm',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Stardew+Season',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Stardew+Mines',
                ],
            ],
            [
                'title' => 'Hades',
                'slug' => 'hades',
                'description' => 'Defy the god of the dead as you hack and slash out of the Underworld.',
                'about' => 'Hades is a god-like rogue-like dungeon crawler that combines the best aspects of Supergiant\'s critically acclaimed titles, including the fast-paced action of Bastion, the rich atmosphere and depth of Transistor, and the character-driven storytelling of Pyre. Winner of over 50 Game of the Year awards.',
                'price' => 24.99,
                'discount_price' => 14.99,
                'discount_pct' => 40,
                'is_discounted' => true,
                'release_date' => '2020-09-17',
                'developer' => 'Supergiant Games',
                'publisher' => 'Supergiant Games',
                'genre' => 'Action',
                'platforms' => ['windows', 'mac', 'playstation', 'xbox', 'nintendo'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Hades',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Hades',
                'rating_avg' => 4.8,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Dual Core 2.4 GHz | RAM: 4 GB | GPU: 1GB VRAM | Storage: 15 GB',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Dual Core 3.0 GHz+ | RAM: 8 GB | GPU: NVIDIA GTX 650 | Storage: 20 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Hades+Combat',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Hades+Boss',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Hades+Dialogue',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Hades+Underworld',
                ],
            ],
            [
                'title' => 'DOOM Eternal',
                'slug' => 'doom-eternal',
                'description' => 'Hell\'s armies have invaded Earth. Become the Slayer.',
                'about' => 'DOOM Eternal is the direct sequel to the award-winning and best-selling DOOM (2016). Experience the ultimate combination of speed and power in DOOM Eternal - the next leap in push-forward, first-person combat. Developed by id Software, DOOM Eternal delivers the ultimate combination of speed and power.',
                'price' => 59.99,
                'discount_price' => 14.99,
                'discount_pct' => 75,
                'is_discounted' => true,
                'release_date' => '2020-03-20',
                'developer' => 'id Software',
                'publisher' => 'Bethesda Softworks',
                'genre' => 'FPS',
                'platforms' => ['windows', 'playstation', 'xbox', 'nintendo'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=DOOM+Eternal',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=DOOM+Eternal',
                'rating_avg' => 4.6,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-2400 / AMD Ryzen 5 1600 | RAM: 8 GB | GPU: NVIDIA GTX 970 4GB / AMD RX 470 4GB | Storage: 50 GB HDD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-6700K / AMD Ryzen 7 1800X | RAM: 16 GB | GPU: NVIDIA GTX 1080 8GB / AMD RX 5700 XT | Storage: 50 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=DOOM+Rip+and+Tear',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=DOOM+Glory+Kill',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=DOOM+Hell',
                ],
            ],
            [
                'title' => 'Resident Evil 4',
                'slug' => 'resident-evil-4',
                'description' => 'A survival horror masterpiece reimagined. Save Ashley Graham.',
                'about' => 'Survival is just the beginning. Six years have passed since the biological disaster in Raccoon City. Leon S. Kennedy, one of the survivors, tracks the kidnapped U.S. president\'s daughter to a secluded European village, where there is something terribly wrong with the locals.',
                'price' => 59.99,
                'discount_price' => 35.99,
                'discount_pct' => 40,
                'is_discounted' => true,
                'release_date' => '2023-03-24',
                'developer' => 'Capcom',
                'publisher' => 'Capcom',
                'genre' => 'Horror',
                'platforms' => ['windows', 'mac', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=RE4',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Resident+Evil+4+Remake',
                'rating_avg' => 4.7,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-7500 / AMD Ryzen 3 1200 | RAM: 8 GB | GPU: NVIDIA GTX 1050 Ti / AMD RX 560 | Storage: 60 GB SSD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-8700 / AMD Ryzen 5 3600 | RAM: 16 GB | GPU: NVIDIA RTX 2060 / AMD RX 5700 | Storage: 60 GB NVMe SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=RE4+Leon',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=RE4+Village',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=RE4+Action',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=RE4+Horror',
                ],
            ],
            [
                'title' => 'Marvel\'s Spider-Man Remastered',
                'slug' => 'spider-man-remastered',
                'description' => 'Be Spider-Man. Experience the rise of Miles Morales.',
                'about' => 'In Marvel\'s Spider-Man Remastered, the worlds of Peter Parker and Spider-Man collide in an original action-packed story. Play as an experienced Peter Parker, fighting big crime and iconic villains in Marvel\'s New York. Web-swing through vibrant neighborhoods and defeat villains with epic, blockbuster action.',
                'price' => 59.99,
                'discount_price' => 39.99,
                'discount_pct' => 33,
                'is_discounted' => true,
                'release_date' => '2022-08-12',
                'developer' => 'Insomniac Games',
                'publisher' => 'Sony Interactive Entertainment',
                'genre' => 'Action',
                'platforms' => ['windows', 'playstation'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Spider-Man',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Marvel%27s+Spider-Man+Remastered',
                'rating_avg' => 4.8,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-4670 / AMD FX 8350 | RAM: 16 GB | GPU: NVIDIA GTX 950 2GB | Storage: 75 GB HDD',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i5-8400 / AMD Ryzen 5 2600 | RAM: 16 GB | GPU: NVIDIA RTX 2060 6GB | Storage: 75 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Spider-Man+NYC',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Spider-Man+Swing',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=Spider-Man+Combat',
                ],
            ],
            [
                'title' => 'Monster Hunter: World',
                'slug' => 'monster-hunter-world',
                'description' => 'Welcome to a new world. Hunt ferocious monsters in living, breathing ecosystems.',
                'about' => 'Welcome to a new world! Take on the role of a hunter and slay ferocious monsters in a living, breathing ecosystem where you can use the surrounding terrain and wildlife to your advantage. Hunt solo or in a party with friends to earn rewards that you can use to craft a huge variety of weapons and armor.',
                'price' => 29.99,
                'discount_price' => 9.99,
                'discount_pct' => 67,
                'is_discounted' => true,
                'release_date' => '2018-08-09',
                'developer' => 'CAPCOM Co., Ltd.',
                'publisher' => 'CAPCOM Co., Ltd.',
                'genre' => 'Action',
                'platforms' => ['windows', 'playstation', 'xbox'],
                'cover' => 'https://placehold.co/300x400/2a475e/1a9fff?text=Monster+Hunter',
                'header' => 'https://placehold.co/1200x400/171a21/1a9fff?text=Monster+Hunter+World',
                'rating_avg' => 4.5,
                'rating_count' => 0,
                'min_req' => 'OS: Windows 7 64-bit | CPU: Intel Core i5-4460 / AMD FX-6300 | RAM: 8 GB | GPU: NVIDIA GTX 760 / AMD R7 260x | Storage: 48 GB',
                'rec_req' => 'OS: Windows 10 64-bit | CPU: Intel Core i7-3770 / AMD FX-9590 | RAM: 8 GB | GPU: NVIDIA GTX 1060 | Storage: 48 GB SSD',
                'screenshots' => [
                    'https://placehold.co/800x450/2a475e/1a9fff?text=MHW+Hunt',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=MHW+Monster',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=MHW+Multiplayer',
                    'https://placehold.co/800x450/2a475e/1a9fff?text=MHW+Environment',
                ],
            ],
        ];
    }
}
