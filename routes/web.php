<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ChatbotController as AdminChatbotController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EisController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\PasswordResetController;
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
Route::view('/syarat', 'pages.policy', [
    'title' => 'Syarat & Ketentuan',
    'sections' => [
        'Akun' => 'Kamu bertanggung jawab menjaga kerahasiaan password dan semua aktivitas di akunmu.',
        'Harga dan stok' => 'Harga dan stok mengikuti data saat checkout. Pesanan hanya dibuat bila stok masih mencukupi.',
        'Pesanan' => 'Kamu bisa membatalkan pesanan selama statusnya Menunggu atau Diproses. Pesanan yang sudah dikirim tidak bisa dibatalkan.',
        'Ulasan' => 'Ulasan bisa ditulis setelah pesanan selesai. Admin dapat menghapus ulasan yang melanggar aturan.',
    ],
])->name('pages.terms');
Route::view('/privasi', 'pages.policy', [
    'title' => 'Kebijakan Privasi',
    'sections' => [
        'Data yang kami simpan' => 'Kami menyimpan email, username, alamat pengiriman, nomor telepon, riwayat pesanan, wishlist, dan ulasanmu. Password disimpan dalam bentuk hash. Bila masuk dengan Google, kami juga menyimpan ID akun Google-mu.',
        'Chatbot' => 'Pertanyaan yang kamu kirim ke chatbot diteruskan ke penyedia layanan AI dan sebagian disimpan sebagai cache jawaban. Saat kamu menanyakan pesanan, nomor, status, isi, total, dan resi pesananmu ikut diteruskan, tanpa alamat dan nomor telepon. Jangan menulis data pribadi di chat. Riwayat chat tersimpan di browsermu.',
        'Penggunaan data' => 'Data dipakai untuk memproses pesanan dan menjalankan fitur toko. Kami tidak menjual datamu.',
    ],
])->name('pages.privacy');
Route::view('/retur', 'pages.policy', [
    'title' => 'Pembayaran & Retur',
    'sections' => [
        'Pembayaran' => 'Tersedia Transfer Bank (Virtual Account), QRIS, dan Bayar di Tempat (COD). Semua pembayaran berjalan dalam mode simulasi.',
        'Pembatalan' => 'Batalkan pesanan dari halaman detail pesanan selama statusnya Menunggu atau Diproses. Stok dan kuota voucher otomatis dikembalikan.',
        'Retur' => 'Barang rusak atau tidak sesuai bisa diajukan untuk diganti maksimal 7 hari setelah diterima dengan menghubungi kami. Pengajuan retur belum tersedia di dalam aplikasi.',
    ],
])->name('pages.returns');
Route::get('/promo', [CatalogController::class, 'promo'])->name('promo');
Route::get('/kategori', [CatalogController::class, 'categories'])->name('categories.index');
Route::get('/search/suggest', [CatalogController::class, 'suggest'])->middleware('throttle:search')->name('search.suggest');
Route::get('/products/{product}', [CatalogController::class, 'show'])->name('products.show');

// Chatbot konsumen (boleh diakses tanpa login).
Route::post('/chatbot', [ChatbotController::class, 'ask'])
    ->middleware('throttle:chatbot')
    ->name('chatbot.ask');
Route::post('/chatbot/feedback', [ChatbotController::class, 'feedback'])
    ->middleware('throttle:chatbot')
    ->name('chatbot.feedback');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleLoginController::class, 'redirect'])
        ->middleware('throttle:login')
        ->name('auth.google');
    Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback'])
        ->middleware('throttle:login')
        ->name('auth.google.callback');
    Route::get('/lupa-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'store'])
        ->middleware('throttle:password')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:password')
        ->name('password.update');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:register');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::get('/account', [\App\Http\Controllers\AccountController::class, 'show'])->name('account');
    Route::put('/account/profile', [\App\Http\Controllers\AccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/account/password', [\App\Http\Controllers\AccountController::class, 'updatePassword'])->name('account.password');
    Route::post('/account/addresses', [\App\Http\Controllers\AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::patch('/account/addresses/{address}/default', [\App\Http\Controllers\AccountController::class, 'setDefaultAddress'])->name('account.addresses.default');
    Route::delete('/account/addresses/{address}', [\App\Http\Controllers\AccountController::class, 'deleteAddress'])->name('account.addresses.destroy');
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
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/pembayaran-berhasil', [OrderController::class, 'paid'])->name('orders.paid');
    Route::post('/orders/{order}/reviews', [ReviewController::class, 'storeForOrder'])->name('orders.reviews.store');
    Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder'])->name('orders.reorder');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/simulate-payment', [OrderController::class, 'simulatePayment'])->name('orders.simulatePayment');
    Route::post('/orders/{order}/simulate-ship', [OrderController::class, 'simulateShip'])->name('orders.simulateShip');
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/eis', [EisController::class, 'index'])->name('eis.index');
    Route::post('/eis/sync', [EisController::class, 'sync'])->middleware('throttle:10,1')->name('eis.sync');
    Route::get('/eis/{period}', [EisController::class, 'show'])->where('period', '\d{4}-\d{2}')->name('eis.show');
    Route::resource('categories', AdminCategoryController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('products', AdminProductController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::resource('vouchers', AdminVoucherController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/export', [AdminOrderController::class, 'export'])->name('orders.export');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('/chatbot', [AdminChatbotController::class, 'index'])->name('chatbot.index');
    Route::delete('/chatbot/purge', [AdminChatbotController::class, 'purge'])->name('chatbot.purge');
    Route::delete('/chatbot/{chatbotMessage}', [AdminChatbotController::class, 'destroy'])->name('chatbot.destroy');
});

// Halaman uji kartu produk dengan data ekstrem, hanya ada di lingkungan lokal.
if (app()->environment('local')) {
    Route::get('/_dev/break-ui', function (\Illuminate\Http\Request $request) {
        $states = \App\Support\Dev\ProductCardFixtures::STATES;
        $state = array_key_exists($request->query('data'), $states) ? $request->query('data') : 'demo';

        return view('dev.break-ui', [
            'states' => $states,
            'state' => $state,
            'products' => \App\Support\Dev\ProductCardFixtures::for($state),
            'wishlistedProductIds' => [],
        ]);
    })->name('dev.break-ui');
}
