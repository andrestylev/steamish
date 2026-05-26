<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Game;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook events.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (! $signature) {
            return response()->json(['error' => 'Missing Stripe signature'], 400);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('stripe.webhook_secret')
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Handle the event
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $userId = $session->metadata->user_id ?? null;
            $sessionId = $session->id;

            if (! $userId) {
                return response()->json(['error' => 'Missing user_id in session metadata'], 400);
            }

            // Retrieve line items from the session
            try {
                $stripe = new \Stripe\StripeClient(config('stripe.secret'));
                $lineItems = $stripe->checkout->sessions->allLineItems($sessionId);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Could not retrieve line items'], 500);
            }

            $fulfilledCount = 0;

            foreach ($lineItems->data as $lineItem) {
                // Extract game_id from metadata or match by product name
                // For simplicity, match cart items by user
                $cartItems = CartItem::where('user_id', $userId)->get();

                foreach ($cartItems as $cartItem) {
                    // Check if already purchased (idempotency)
                    $existing = Purchase::where('stripe_session_id', $sessionId)
                        ->where('game_id', $cartItem->game_id)
                        ->first();

                    if (! $existing) {
                        $game = $cartItem->game;
                        $amountPaid = $game ? ($game->is_discounted ? $game->discount_price : $game->price) : 0;

                        Purchase::create([
                            'user_id' => $userId,
                            'game_id' => $cartItem->game_id,
                            'stripe_session_id' => $sessionId,
                            'amount_paid' => $amountPaid,
                        ]);
                    }

                    // Remove from cart
                    $cartItem->delete();
                    $fulfilledCount++;
                }
            }

            return response()->json([
                'status' => 'success',
                'fulfilled' => $fulfilledCount,
            ]);
        }

        // Acknowledge other event types
        return response()->json(['status' => 'received']);
    }
}
