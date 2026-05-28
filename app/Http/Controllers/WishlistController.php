<?php

namespace App\Http\Controllers;

use App\Models\WishlistItem;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    use HasGameData;

    /**
     * Show the user's wishlist with game details.
     */
    public function index(): Response
{
    $items = WishlistItem::where('user_id', Auth::id())
        ->with('game.images')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'game_id' => $item->game_id,
                'game' => $item->game,
            ];
        })->values();

    return Inertia::render('Wishlist', [
        'items' => $items,
        'totalItems' => $items->count(),
    ]);
}

    /**
     * Toggle a game in the user's wishlist.
     */
    public function toggle(int $gameId): RedirectResponse
    {
        // Validate game exists in hardcoded data
        $game = Game::find($gameId);

if (! $game) {
    return redirect()->back()->with('error', __('Game not found.'));
}

        $existing = WishlistItem::where('user_id', Auth::id())
            ->where('game_id', $gameId)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = __('Game removed from wishlist.');
        } else {
            WishlistItem::create([
                'user_id' => Auth::id(),
                'game_id' => $gameId,
            ]);
            $message = __('Game added to wishlist.');
        }

        return redirect()->back()->with('success', $message);
    }
}
