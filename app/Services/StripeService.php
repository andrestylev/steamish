<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\User;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session for the user's cart items.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CartItem>  $cartItems
     * @return \Stripe\Checkout\Session
     */
    public function createCheckoutSession($cartItems, User $user): Session
    {
        $lineItems = $cartItems->map(function (CartItem $item) {
            $game = $item->game;
            $price = $game->is_discounted ? $game->discount_price : $game->price;

            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $game->title,
                        'images' => array_filter([$game->cover]),
                    ],
                    'unit_amount' => (int) round($price * 100), // cents
                ],
                'quantity' => 1,
            ];
        })->toArray();

        return Session::create([
            'mode' => 'payment',
            'customer_email' => $user->email,
            'line_items' => $lineItems,
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
            'success_url' => route('library.index') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('cart.index'),
        ]);
    }

    /**
     * Verify a Stripe webhook signature and return the event.
     *
     * @throws SignatureVerificationException
     * @return \Stripe\Event
     */
    public function verifyWebhookSignature(string $payload, string $signature): \Stripe\Event
    {
        $webhookSecret = config('stripe.webhook_secret');

        return Webhook::constructEvent($payload, $signature, $webhookSecret);
    }
}
