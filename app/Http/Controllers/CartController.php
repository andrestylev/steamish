<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    use HasGameData;

    /**
     * Show the user's cart.
     */
    public function index(): Response
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with('game')
            ->get();

        $subtotal = $cartItems->sum(function ($item) {
            $game = $item->game;
            return $game ? (float) ($game->is_discounted ? $game->discount_price : $game->price) : 0;
        });

        return Inertia::render('Cart', [
            'cartItems' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'game_id' => $item->game_id,
                    'game' => $item->game ? [
                        'id' => $item->game->id,
                        'title' => $item->game->title,
                        'slug' => $item->game->slug,
                        'cover' => $item->game->cover,
                        'price' => $item->game->price,
                        'discount_price' => $item->game->discount_price,
                        'discount_pct' => $item->game->discount_pct,
                        'is_discounted' => $item->game->is_discounted,
                        'genre' => $item->game->genre,
                    ] : null,
                ];
            }),
            'subtotal' => $subtotal,
            'itemCount' => $cartItems->count(),
        ]);
    }

    /**
     * Add a game to the cart.
     */
    public function add(int $gameId): RedirectResponse
    {
        $game = Game::find($gameId);

        if (! $game) {
            return redirect()->back()->with('error', __('Game not found.'));
        }

        $existing = CartItem::where('user_id', Auth::id())
            ->where('game_id', $gameId)
            ->first();

        if (! $existing) {
            CartItem::create([
                'user_id' => Auth::id(),
                'game_id' => $gameId,
            ]);
        }

        session()->flash('added_to_cart', (int) $gameId);

        return redirect()->back()->with('success', __('Game added to cart.'));
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(CartItem $item): RedirectResponse
    {
        if ($item->user_id !== Auth::id()) {
            abort(403);
        }

        $item->delete();

        return redirect()->back()->with('success', __('Game removed from cart.'));
    }
}
