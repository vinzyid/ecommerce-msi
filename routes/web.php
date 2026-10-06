<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ChatbotController as AdminChatbotController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::view('/tentang', 'pages.about')->name('about');
Route::get('/promo', [CatalogController::class, 'promo'])->name('promo');
Route::get('/kategori', [CatalogController::class, 'categories'])->name('categories.index');
Route::get('/products/{product}', [CatalogController::class, 'show'])->name('products.show');

// Chatbot konsumen (boleh diakses tanpa login).
Route::post('/chatbot', [ChatbotController::class, 'ask'])->name('chatbot.ask');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/account', [\App\Http\Controllers\AccountController::class, 'show'])->name('account');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart', [CartController::class, 'destroyAll'])->name('cart.destroyAll');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::post('/voucher', [VoucherController::class, 'apply'])->name('voucher.apply');
    Route::delete('/voucher', [VoucherController::class, 'remove'])->name('voucher.remove');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::delete('/wishlist', [WishlistController::class, 'destroyAll'])->name('wishlist.destroyAll');

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('categories', AdminCategoryController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('products', AdminProductController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::resource('vouchers', AdminVoucherController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('/chatbot', [AdminChatbotController::class, 'index'])->name('chatbot.index');
    Route::delete('/chatbot/purge', [AdminChatbotController::class, 'purge'])->name('chatbot.purge');
    Route::delete('/chatbot/{chatbotMessage}', [AdminChatbotController::class, 'destroy'])->name('chatbot.destroy');
});
