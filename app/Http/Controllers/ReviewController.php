<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a newly created review.
     *
     * Only users who have purchased the game may submit a review.
     * Duplicate reviews (same user + game) are rejected.
     */
    public function store(Request $request, Game $game): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        // Must be authenticated
        if (! $user) {
            return redirect()->route('login');
        }

        // Must have purchased the game
        $hasPurchased = Purchase::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->exists();

        if (! $hasPurchased) {
            return redirect()->back()->with('error', __('You must own this game to review it.'));
        }

        // Must not already have a review
        $existing = Review::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->exists();

        if ($existing) {
            return redirect()->back()->with('error', __('You have already reviewed this game.'));
        }

        // Create the review
        Review::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'rating' => $validated['rating'],
            'body' => $validated['body'],
            'hours_played' => 0,
            'is_recommended' => $validated['rating'] >= 3,
        ]);

        // Update game rating average and count
        $game->rating_count = Review::where('game_id', $game->id)->count();
        $game->rating_avg = Review::where('game_id', $game->id)->avg('rating');
        $game->save();

        return redirect()->back()->with('success', __('Review submitted successfully.'));
    }
}
