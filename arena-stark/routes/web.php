<?php

use App\Inventory\Http\Controllers\EventController;
use App\Orders\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

// Inventory context — public event listing
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');

// Orders context — ticket purchase flow (requires auth)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::post('/orders/reserve', [OrderController::class, 'reserve'])->name('orders.reserve');
    Route::get('/orders/confirm/{token}', [OrderController::class, 'confirmView'])->name('orders.confirm-view');
    Route::post('/orders/confirm/{token}', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::get('/orders/success/{order}', [OrderController::class, 'success'])->name('orders.success');
});

require __DIR__.'/settings.php';
