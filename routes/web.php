<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ShopController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to shop
Route::get('/', [ShopController::class, 'index']);

// Shop routes
Route::prefix('loja')->name('shop.')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/produto/{id}', [ShopController::class, 'show'])->name('product.show');
    Route::get('/categoria/{slug}', [ShopController::class, 'category'])->name('category');
    Route::get('/carrinho', [ShopController::class, 'cart'])->name('cart');
    Route::get('/checkout', [ShopController::class, 'checkout'])->name('checkout');
});

// Alternative routes without prefix
Route::get('/produtos', [ShopController::class, 'index'])->name('products');
Route::get('/produtos/{id}', [ShopController::class, 'show'])->name('product.details');

// Demo and development routes
Route::get('/demo', [ShopController::class, 'index'])->name('demo');
Route::get('/frontend', [ShopController::class, 'index'])->name('frontend');
