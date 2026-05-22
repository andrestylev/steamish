<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Inertia SPA routes — all pages served through the app layout.
|
*/

Route::get('/', function () {
    return \Inertia\Inertia::render('Home');
})->name('home');

// Auth routes (login, register, logout)
require __DIR__.'/auth.php';

// Authenticated routes
Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
