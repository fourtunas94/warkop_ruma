<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\Admin\KategoriApiController;
use App\Http\Controllers\Api\Admin\MenuApiController;
use App\Http\Controllers\Api\Admin\MejaApiController;
use App\Http\Controllers\Api\Admin\UsersApiController;
use App\Http\Controllers\Api\Admin\RiwayatApiController;
use App\Http\Controllers\Api\Admin\PromoApiController;
use App\Http\Controllers\Api\Admin\PengaturanApiController;
use App\Http\Controllers\Api\Admin\StokApiController;
use App\Http\Controllers\Api\Kasir\KasirDashboardApiController;
use App\Http\Controllers\Api\Kasir\KasirPosDashboardApiController;
use App\Http\Controllers\Api\Kasir\KasirRiwayatApiController;

// Auth API
Route::post('/auth/login', [AuthApiController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    // Kasir routes (kasir and admin can access)
    Route::middleware(['role.api:kasir,admin'])->prefix('kasir')->group(function () {
        Route::get('/dashboard', [KasirDashboardApiController::class, 'index']);
        Route::get('/pos-dashboard', [KasirPosDashboardApiController::class, 'index']);
        Route::post('/telah-bayar/{id}', [KasirDashboardApiController::class, 'telahBayar']);
        Route::post('/siap/{id}', [KasirDashboardApiController::class, 'siap']);
        Route::get('/cek-baru/{last_id}', [KasirDashboardApiController::class, 'cekBaru']);
        Route::get('/cek-pembayaran-berhasil', [KasirDashboardApiController::class, 'cekPembayaranBerhasil']);
        Route::get('/riwayat', [KasirRiwayatApiController::class, 'index']);

        Route::middleware(['role.api:kasir'])->group(function () {
            Route::post('/pesanan/{id}/status', [KasirPosDashboardApiController::class, 'updateStatusPesanan']);
            Route::post('/pesanan/{id}/split-bill', [KasirPosDashboardApiController::class, 'createSplitBill']);
            Route::post('/pesanan/{id}/split-bill/bayar-item', [KasirPosDashboardApiController::class, 'bayarSebagianItem']);
            Route::post('/pesanan/{id}/split-bill/{splitId}/bayar', [KasirPosDashboardApiController::class, 'bayarSplit']);
            Route::delete('/pesanan/{id}/split-bill', [KasirPosDashboardApiController::class, 'resetSplitBill']);
            Route::post('/pesanan/{id}/merge-bill', [KasirPosDashboardApiController::class, 'mergeBill']);
        });
    });

    // Admin management routes are also available to kasir accounts.
    Route::middleware(['role.api:admin,kasir'])->prefix('admin')->group(function () {
        // Kategori
        Route::get('/kategori', [KategoriApiController::class, 'index']);
        Route::post('/kategori', [KategoriApiController::class, 'store']);
        Route::put('/kategori/{id}', [KategoriApiController::class, 'update']);
        Route::delete('/kategori/{id}', [KategoriApiController::class, 'destroy']);
        Route::post('/kategori/{id}/toggle', [KategoriApiController::class, 'toggleStatus']);

        // Menu
        Route::get('/menu', [MenuApiController::class, 'index']);
        Route::post('/menu', [MenuApiController::class, 'store']);
        Route::put('/menu/{id}', [MenuApiController::class, 'update']);
        Route::post('/menu/{id}', [MenuApiController::class, 'update']);
        Route::delete('/menu/{id}', [MenuApiController::class, 'destroy']);
        Route::post('/menu/{id}/toggle', [MenuApiController::class, 'toggleStatus']);
        Route::post('/menu/{id}/toggle-stok', [MenuApiController::class, 'toggleStok']);

        // Meja
        Route::get('/meja', [MejaApiController::class, 'index']);
        Route::post('/meja', [MejaApiController::class, 'store']);
        Route::delete('/meja/{id}', [MejaApiController::class, 'destroy']);
        Route::post('/meja/{id}/toggle', [MejaApiController::class, 'toggleStatus']);

        // Users
        Route::get('/users', [UsersApiController::class, 'index']);
        Route::post('/users', [UsersApiController::class, 'store']);
        Route::delete('/users/{id}', [UsersApiController::class, 'destroy']);

        // Riwayat
        Route::get('/riwayat', [RiwayatApiController::class, 'index']);

        // Promo
        Route::get('/promo', [PromoApiController::class, 'index']);
        Route::post('/promo', [PromoApiController::class, 'store']);
        Route::put('/promo/{id}', [PromoApiController::class, 'update']);
        Route::post('/promo/{id}/toggle', [PromoApiController::class, 'toggle']);
        Route::delete('/promo/{id}', [PromoApiController::class, 'destroy']);

        // Pengaturan
        Route::get('/pengaturan', [PengaturanApiController::class, 'index']);
        Route::post('/pengaturan', [PengaturanApiController::class, 'update']);

        // Stok & Pembelian Barang
        Route::get('/stok', [StokApiController::class, 'index']);
        Route::post('/stok', [StokApiController::class, 'store']);
        Route::put('/stok/{id}', [StokApiController::class, 'update']);
        Route::delete('/stok/{id}', [StokApiController::class, 'destroy']);
        Route::post('/stok/pembelian', [StokApiController::class, 'catatPembelian']);
        Route::get('/stok/riwayat-pembelian', [StokApiController::class, 'riwayatPembelian']);
    });
});
