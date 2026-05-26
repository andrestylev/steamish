<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Create a Stripe Checkout Session and redirect to Stripe.
     */
    public function index(): RedirectResponse
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with('game')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('Your cart is empty.'));
        }

        /** @var StripeService $stripeService */
        $stripeService = app(StripeService::class);

        try {
            $session = $stripeService->createCheckoutSession($cartItems, Auth::user());

            return Inertia::location($session->url);
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', __('Payment could not be initiated. Please try again.'));
        }
    }
}
