<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductStockHistoryController;

// Route untuk login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');  // Menampilkan form login
Route::post('/login', [AuthController::class, 'login']);  // Menyimpan proses login

// Route untuk register
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');  // Menampilkan form registrasi
Route::post('/register', [AuthController::class, 'register']);  // Menyimpan data registrasi

// Route untuk logout - hanya satu versi rute yang diperlukan
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');  // Logout pengguna

// Route untuk dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/analytics/timeseries',[DashboardController::class, 'timeseries'])->name('dashboard.analytics.timeseries');
Route::get('/dashboard/analytics/top-products',[DashboardController::class, 'topProducts'])->name('dashboard.analytics.top_products');

// Route default untuk root ("/") untuk mengarahkan ke dashboard
Route::get('/', function() {
    return redirect()->route('dashboard');  // Redirect ke dashboard sebagai halaman utama
});

// Rute untuk user
Route::resource('users', UserController::class); // Menggunakan resource controller

// Rute untuk profil pengguna
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');  // Menampilkan profil
    Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');  // Memperbarui profil
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');  // Mengubah password
});

// Route untuk kategori produk
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');  // Menampilkan kategori produk
Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');  // Form untuk menambah kategori produk
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');  // Menyimpan kategori produk baru
Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');  // Form untuk mengedit kategori produk
Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');  // Memperbarui kategori produk
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');  // Menghapus kategori produk

// Route untuk unit produk
Route::get('/units', [CategoryController::class, 'indexUnits'])->name('units.index');  // Menampilkan unit produk
Route::get('/units/create', [CategoryController::class, 'createUnit'])->name('units.create');  // Form untuk menambah unit produk
Route::post('/units', [CategoryController::class, 'storeUnit'])->name('units.store');  // Menyimpan unit produk baru
Route::get('/units/{id}/edit', [CategoryController::class, 'editUnit'])->name('units.edit');  // Form untuk mengedit unit produk
Route::put('/units/{id}', [CategoryController::class, 'updateUnit'])->name('units.update');  // Memperbarui unit produk
Route::delete('/units/{id}', [CategoryController::class, 'destroyUnit'])->name('units.destroy');  // Menghapus unit produk

// Route untuk supplier
Route::get('/suppliers', [CategoryController::class, 'indexSuppliers'])->name('suppliers.index');  // Menampilkan supplier
Route::get('/suppliers/create', [CategoryController::class, 'createSupplier'])->name('suppliers.create');  // Form untuk menambah supplier
Route::post('/suppliers', [CategoryController::class, 'storeSupplier'])->name('suppliers.store');  // Menyimpan supplier baru
Route::get('/suppliers/{id}/edit', [CategoryController::class, 'editSupplier'])->name('suppliers.edit');  // Form untuk mengedit supplier
Route::put('/suppliers/{id}', [CategoryController::class, 'updateSupplier'])->name('suppliers.update'); // Memperbarui supplier
Route::delete('/suppliers/{id}', [CategoryController::class, 'destroySupplier'])->name('suppliers.destroy');  // Menghapus supplier

// Route untuk produk
Route::get('/products', [ProductController::class, 'index'])->name('products.index');  // Menampilkan produk
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');  // Form untuk menambah produk
Route::post('/products', [ProductController::class, 'store'])->name('products.store');  // Menyimpan produk baru
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');  // Menampilkan detail produk
Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');  // Form untuk mengedit produk
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');  // Memperbarui produk
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');  // Menghapus produk
Route::get('/products/by-supplier/{supplier}', [ProductController::class, 'getBySupplier']);
// Tambah stok untuk produk tertentu
Route::get('/products/{product}/add-stock', [ProductController::class, 'addStock'])->name('products.add-stock');
Route::post('/products/{product}/add-stock', [ProductController::class, 'storeStock'])->name('products.store-stock');
Route::get('/stock-histories/{history}/edit', [ProductStockHistoryController::class, 'edit'])->name('stock-histories.edit');
Route::put('/stock-histories/{history}', [ProductStockHistoryController::class, 'update'])->name('stock-histories.update');
Route::delete('/stock-histories/{history}', [ProductStockHistoryController::class, 'destroy'])->name('stock-histories.destroy');

// Route untuk pelanggan (customer)
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');  // Menampilkan pelanggan
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');  // Form untuk menambah pelanggan
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');  // Menyimpan pelanggan baru
Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');  // Form untuk mengedit pelanggan
Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');  // Memperbarui pelanggan
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');  // Menghapus pelanggan

// Route untuk transaksi
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');  // Menampilkan transaksi
Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');  // Form untuk menambah transaksi
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');  // Menyimpan transaksi baru
Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');  // Menampilkan detail transaksi
Route::get('/transactions/{id}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');  // Form untuk mengedit transaksi
Route::put('/transactions/{id}', [TransactionController::class, 'update'])->name('transactions.update');  // Memperbarui transaksi
Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');  // Menghapus transaksi
Route::get('/transactions/print/{transaction}', [TransactionController::class, 'print'])->name('transactions.print');


// Route untuk laporan
Route::get('/report', [ReportController::class, 'index'])->name('report.index');  // Menampilkan laporan
Route::get('/report/filter', [ReportController::class, 'filter'])->name('report.filter');  // Menampilkan laporan filter
Route::get('/report/print/{start_date}/{end_date}', [ReportController::class, 'print'])->name('report.print');  // Mencetak laporan berdasarkan rentang tanggal

// Route untuk order (pesanan)
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');  // Menampilkan daftar pesanan
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');  // Form untuk menambah pesanan
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');  // Menyimpan pesanan baru
Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');  // Menampilkan detail pesanan
Route::get('/orders/{id}/edit', [OrderController::class, 'edit'])->name('orders.edit');  // Form untuk mengedit pesanan
Route::put('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');  // Memperbarui pesanan
Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');  // Menghapus pesanan
Route::get('/orders/{id}/print', [OrderController::class, 'printOrder'])->name('orders.print');  // Mencetak pesanan

// Route untuk order report
Route::get('/order-report', [OrderReportController::class, 'index'])->name('order_report.index');
Route::get('/order-report/filter', [OrderReportController::class, 'filter'])->name('order_report.filter');
Route::get('/order-report/print/{startDate}/{endDate}', [OrderReportController::class, 'print'])->name('order_report.print');

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
