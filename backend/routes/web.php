<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PublicMonitorController;

// Public Monitor
Route::get('/', [PublicMonitorController::class, 'index']);
Route::get('/monitor', [PublicMonitorController::class, 'index']);
Route::get('/monitor/data', [PublicMonitorController::class, 'data']);

// Authentication
Route::get('/auth', [AuthController::class, 'index']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth.web');

// Customer Order
Route::get('/order/{kode}', [OrderController::class, 'menu']);
Route::get('/order/checkout/{kode}', [OrderController::class, 'checkout']);
Route::post('/order/cek-promo/{kode}', [OrderController::class, 'cekPromo']);
Route::post('/order/submit/{kode}', [OrderController::class, 'submit']);
Route::get('/order/sukses/{kode}', [OrderController::class, 'sukses']);
Route::post('/order/midtrans-notification', [OrderController::class, 'midtransNotification']);
