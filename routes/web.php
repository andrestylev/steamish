<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Inertia SPA routes — all pages served through the app layout.
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/games/{game:slug}', [GameController::class, 'show'])->name('games.show');

// Auth routes (login, register, logout)
require __DIR__.'/auth.php';

// Stripe webhook (no CSRF — excluded in bootstrap/app.php)
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

// Authenticated routes
Route::middleware('auth')->group(function (): void {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{gameId}', [CartController::class, 'add'])->name('cart.add')->whereNumber('gameId');
    Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');

    // Library
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');

    // Wishlist (placeholder route — to be implemented in Phase 5)
    Route::post('/wishlist/{gameId}', [WishlistController::class, 'toggle'])->name('wishlist.toggle')->whereNumber('gameId');
});
