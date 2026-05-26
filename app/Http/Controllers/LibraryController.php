<?php

namespace App\Http\Controllers;

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
        // Get purchased game IDs from the database
        $purchasedGameIds = Purchase::where('user_id', Auth::id())
            ->pluck('game_id')
            ->unique()
            ->toArray();

        // Since we're using hardcoded data (no seeders yet), purchasedGameIds
        // will be empty until purchases are made via Stripe webhook.
        // For demo purposes, we filter the hardcoded games by purchased IDs.
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
