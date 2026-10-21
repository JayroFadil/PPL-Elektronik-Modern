<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PromoController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;

/*
|--------------------------------------------------------------------------
| Public Routes (Guest)
|--------------------------------------------------------------------------
*/

// Halaman utama (publik)
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin')
            return redirect()->route('admin.index');
        if ($user->role === 'owner')
            return redirect()->route('owner.index');
    }
    return app(ProdukController::class)->index();
})->name('index');

// Katalog produk (publik - bisa browsing tanpa login)
Route::get('/products', function (Illuminate\Http\Request $request) {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin')
            return redirect()->route('admin.index');
        if ($user->role === 'owner')
            return redirect()->route('owner.index');
    }
    return app(ProdukController::class)->catalog($request);
})->name('products.index');

Route::get('/products/{produk}', [ProdukController::class, 'show'])->name('products.show');

// Cart count (AJAX, publik)
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
Route::post('/midtrans/notification', [MidtransNotificationController::class, 'handle'])
    ->name('midtrans.notification');

/*
|--------------------------------------------------------------------------
| Auth Routes (Guest only)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'verifyForgotPasswordEmail'])->name('password.email.verify');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Customer Routes (auth + customer only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:customer'])->group(function () {
    // Keranjang (harus login)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/voucher', [CartController::class, 'applyVoucher'])->name('cart.voucher.apply');
    Route::delete('/cart/voucher', [CartController::class, 'removeVoucher'])->name('cart.voucher.remove');

    // Customer profile & pesanan (hanya customer)
    Route::get('/profile', [UserController::class, 'profile'])->name('customer.profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('customer.profile.update');
    Route::post('/profile/alamat', [UserController::class, 'storeAlamat'])->name('customer.alamat.store');
    Route::put('/profile/alamat/{alamatUser}', [UserController::class, 'updateAlamat'])->name('customer.alamat.update');
    Route::delete('/profile/alamat/{alamatUser}', [UserController::class, 'destroyAlamat'])->name('customer.alamat.destroy');
    Route::get('/my-orders', [PesananController::class, 'customerOrders'])->name('customer.orders');
    Route::post('/my-orders/{pesanan}/payment', [PesananController::class, 'startPayment'])->name('customer.orders.payment');
    Route::post('/my-orders/{pesanan}/complete', [PesananController::class, 'completeOrder'])->name('customer.orders.complete');
    Route::get('/checkout', [PesananController::class, 'checkout'])->name('customer.checkout');
    Route::post('/buy-now', [PesananController::class, 'buyNow'])->name('customer.buyNow');
    Route::post('/checkout', [PesananController::class, 'placeOrder'])->name('customer.placeOrder');
    Route::get('/notifications', [NotifikasiController::class, 'index'])->name('customer.notifications.index');
    Route::patch('/notifications/{notifikasi}/read', [NotifikasiController::class, 'markRead'])->name('customer.notifications.read');
    Route::patch('/notifications/read-all', [NotifikasiController::class, 'markAllRead'])->name('customer.notifications.readAll');
    Route::delete('/notifications/{notifikasi}', [NotifikasiController::class, 'destroy'])->name('customer.notifications.destroy');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('customer.wishlist');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('wishlist.count');

    // Lokasi (AJAX)
    Route::get('/locations/kotas/{id_provinsi}', [\App\Http\Controllers\LocationController::class, 'getKotas']);
    Route::get('/locations/kecamatans/{id_kota}', [\App\Http\Controllers\LocationController::class, 'getKecamatans']);
    Route::get('/locations/desas/{id_kecamatan}', [\App\Http\Controllers\LocationController::class, 'getDesas']);

});

/*
|--------------------------------------------------------------------------
| Admin Routes (auth + admin only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');

    // Kelola Produk
    Route::get('/products', [ProdukController::class, 'adminIndex'])->name('products.index');
    Route::post('/products', [ProdukController::class, 'store'])->name('products.store');
    Route::get('/products/{produk}/edit', [ProdukController::class, 'edit'])->name('products.edit');
    Route::put('/products/{produk}', [ProdukController::class, 'update'])->name('products.update');
    Route::delete('/products/{produk}', [ProdukController::class, 'destroy'])->name('products.destroy');

    // Kelola Kategori
    Route::get('/categories', [KategoriController::class, 'index'])->name('categories.index');
    Route::post('/categories', [KategoriController::class, 'store'])->name('categories.store');
    Route::put('/categories/{kategori}', [KategoriController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{kategori}', [KategoriController::class, 'destroy'])->name('categories.destroy');

    // Kelola Promo
    Route::get('/promos', [PromoController::class, 'index'])->name('promos.index');
    Route::post('/promos', [PromoController::class, 'store'])->name('promos.store');
    Route::put('/promos/{promo}', [PromoController::class, 'update'])->name('promos.update');
    Route::delete('/promos/{promo}', [PromoController::class, 'destroy'])->name('promos.destroy');

    // Kelola Pesanan
    Route::get('/orders', [PesananController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{pesanan}/status', [PesananController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::patch('/orders/{pesanan}/payment', [PesananController::class, 'updatePayment'])->name('orders.updatePayment');
    Route::get('/report/download', [UserController::class, 'downloadReport'])->name('report.download');

    // Kelola User (customer saja)
    Route::get('/users', [UserController::class, 'userList'])->name('users.index');
    Route::post('/users', [UserController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroyUser'])->name('users.destroy');
});

/*
|--------------------------------------------------------------------------
| Owner Routes (auth + owner only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', [UserController::class, 'ownerDashboard'])->name('index');
    Route::get('/report/download', [UserController::class, 'downloadReport'])->name('report.download');
});
