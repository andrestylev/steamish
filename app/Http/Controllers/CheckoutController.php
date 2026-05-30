<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Game;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Show the multi-step checkout page with cart items.
     */
    public function index(): Response|RedirectResponse
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with('game')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('Your cart is empty.'));
        }

        $items = $cartItems->map(function ($item) {
            $game = $item->game;
            $price = $game
                ? (float) ($game->is_discounted ? $game->discount_price : $game->price)
                : 0;

            return [
                'cart_id' => $item->id,
                'game_id' => $game?->id,
                'title' => $game?->title ?? 'Unknown Game',
                'cover' => $game?->cover,
                'price' => $price,
            ];
        });

        return Inertia::render('Checkout', [
            'items' => $items,
            'user' => [
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ]);
    }

    /**
     * Process checkout: create purchases, clear cart, return order number.
     */
    public function process(Request $request): RedirectResponse
    {
        $request->validate([
            'game_ids' => 'required|array|min:1',
            'game_ids.*' => 'exists:games,id',
        ]);

        $user = Auth::user();
        $gameIds = $request->input('game_ids');

        $cartItems = CartItem::where('user_id', $user->id)
            ->whereIn('game_id', $gameIds)
            ->with('game')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', __('No items to process.'));
        }

        DB::transaction(function () use ($user, $cartItems, $gameIds) {
            foreach ($cartItems as $item) {
                if (! $item->game) {
                    continue;
                }

                Purchase::create([
                    'user_id' => $user->id,
                    'game_id' => $item->game_id,
                    'stripe_session_id' => 'manual-' . Str::random(16),
                    'amount_paid' => $item->game->is_discounted
                        ? $item->game->discount_price
                        : $item->game->price,
                ]);
            }

            // Clear purchased items from cart
            CartItem::where('user_id', $user->id)
                ->whereIn('game_id', $gameIds)
                ->delete();
        });

        // Generate a readable order number
        $orderNumber = 'ST-' . now()->format('Ymd') . '-' . Str::random(6);

        return redirect()->route('checkout.index')
            ->with('order_created', true)
            ->with('order_number', $orderNumber);
    }
}
