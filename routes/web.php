<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransaksiController;


/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');

});


/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

});


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    // PRODUK

    Route::get('/produk', [ProdukController::class, 'index'])
        ->name('produk.index');

    Route::get('/produk/tambah', [ProdukController::class, 'create'])
        ->name('produk.create');

    Route::post('/produk', [ProdukController::class, 'store'])
        ->name('produk.store');

    Route::get('/produk/{produk}/edit', [ProdukController::class, 'edit'])
        ->name('produk.edit');

    Route::put('/produk/{produk}', [ProdukController::class, 'update'])
        ->name('produk.update');

    Route::patch('/produk/{produk}/nonaktifkan', [ProdukController::class, 'nonaktifkan'])
        ->name('produk.nonaktifkan');


    // TRANSAKSI

    Route::get('/transaksi', [TransaksiController::class, 'index'])
        ->name('transaksi.index');

    Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'show'])
        ->name('transaksi.show');

    Route::patch('/transaksi/{transaksi}/validasi', [TransaksiController::class, 'validasi'])
        ->name('transaksi.validasi');

    Route::patch('/transaksi/{transaksi}/kirim', [TransaksiController::class, 'kirim'])
        ->name('transaksi.kirim');


    // SALES

    Route::get('/sales', [SalesController::class, 'index'])
        ->name('sales.index');

    Route::get('/sales/tambah', [SalesController::class, 'create'])
        ->name('sales.create');

    Route::post('/sales', [SalesController::class, 'store'])
        ->name('sales.store');

    Route::get('/sales/{sales}/edit', [SalesController::class, 'edit'])
        ->name('sales.edit');

    Route::put('/sales/{sales}', [SalesController::class, 'update'])
        ->name('sales.update');

    Route::patch('/sales/{sales}/nonaktifkan', [SalesController::class, 'nonaktifkan'])
        ->name('sales.nonaktifkan');

    Route::patch('/sales/{sales}/aktifkan', [SalesController::class, 'aktifkan'])
        ->name('sales.aktifkan');

});


/*
|--------------------------------------------------------------------------
| ADMIN + SALES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,sales'])->group(function () {

    // PELANGGAN

    Route::get('/pelanggan', [PelangganController::class, 'index'])
        ->name('pelanggan.index');

    Route::get('/pelanggan/tambah', [PelangganController::class, 'create'])
        ->name('pelanggan.create');

    Route::post('/pelanggan', [PelangganController::class, 'store'])
        ->name('pelanggan.store');

    Route::get('/pelanggan/{pelanggan}/edit', [PelangganController::class, 'edit'])
        ->name('pelanggan.edit');

    Route::put('/pelanggan/{pelanggan}', [PelangganController::class, 'update'])
        ->name('pelanggan.update');

    Route::patch('/pelanggan/{pelanggan}/nonaktifkan', [PelangganController::class, 'nonaktifkan'])
        ->name('pelanggan.nonaktifkan');


    // KIRIM NOTA WHATSAPP

    Route::post(
        '/transaksi/{transaksi}/kirim-nota',
        [TransaksiController::class, 'kirimNota']
    )->name('transaksi.kirimNota');

});


/*
|--------------------------------------------------------------------------
| SALES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:sales'])->group(function () {

    // ORDER

    Route::get('/order', [OrderController::class, 'index'])
        ->name('order.index');

    Route::get('/order/tambah', [OrderController::class, 'create'])
        ->name('order.create');

    Route::get('/order/{order}', [OrderController::class, 'show'])
        ->name('order.show');

    Route::get('/order/{order}/edit', [OrderController::class, 'edit'])
        ->name('order.edit');

    Route::put('/order/{order}', [OrderController::class, 'update'])
        ->name('order.update');

    Route::post('/order', [OrderController::class, 'store'])
        ->name('order.store');

    Route::post('/order/{order}/upload-bukti', [OrderController::class, 'uploadBukti'])
        ->name('order.uploadBukti');

    Route::patch('/order/{order}/selesai', [OrderController::class, 'selesai'])
        ->name('order.selesai');

});


/*
|--------------------------------------------------------------------------
| DIREKTUR
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:direktur'])->group(function () {

    // route khusus Direktur nanti ditaruh di sini

});