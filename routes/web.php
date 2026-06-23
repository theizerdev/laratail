<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// ─── Public Store Routes ─────────────────────────────────────────────────────
Volt::route('/', 'store.home')->name('home');
Volt::route('/producto/{product:slug}', 'store.product-detail')->name('store.product.detail');

// Catalog
Volt::route('/catalogo', 'store.catalog')->name('store.catalog');
Volt::route('/catalogo/{category:slug}', 'store.catalog')->name('store.catalog.category');

// Cart
Volt::route('/carrito', 'store.cart')->name('store.cart');

// Checkout
Route::middleware('auth')->group(function () {
    Volt::route('/checkout', 'store.checkout')->name('store.checkout');
    Volt::route('/checkout/confirmacion/{order}', 'store.checkout-confirmation')->name('store.checkout.confirmation');
});

 
// Customer Auth (guest only)
Route::middleware('guest')->group(function () {
    Volt::route('/acceso', 'store.login')->name('store.login');
    Volt::route('/registro', 'store.register')->name('store.register');
});

// Logout
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('store.logout');

// ─── Customer Account Routes (authenticated only) ────────────────────────────
Route::middleware('auth')->prefix('mi-cuenta')->name('store.account.')->group(function () {
    Volt::route('/', 'store.account.profile')->name('profile');
    Volt::route('/pedidos', 'store.account.orders')->name('orders');
    Volt::route('/pedidos/{order}', 'store.account.order-detail')->name('order-detail');
    Volt::route('/direcciones', 'store.account.addresses')->name('addresses');
});

// Wishlist (auth only)
Route::middleware('auth')->group(function () {
    Volt::route('/favoritos', 'store.wishlist')->name('store.wishlist');
});

// Recently viewed
Volt::route('/vistos-recientemente', 'store.recently-viewed')->name('store.recently-viewed');

// Product comparison
Volt::route('/comparar', 'store.compare')->name('store.compare');

// Order tracking (public)
Volt::route('/rastrear-pedido', 'store.track-order')->name('store.track-order');

// Shared wishlist (public)
Volt::route('/lista-regalos/{customer}', 'store.shared-wishlist')->name('store.shared-wishlist');

Route::middleware('auth')->group(function () {
    Volt::route('/verificar-telefono', 'store.verify-otp')->name('store.verify-otp');
});

// Authentication routes (admin)
require __DIR__.'/auth.php';
