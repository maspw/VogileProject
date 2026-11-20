<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BebanController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PelunasanPiutangController;
use App\Http\Controllers\PembayaranHutangController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\ReturPenjualanController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - PT VOGILE Enterprise System
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/admin');
});

// Autentikasi Pengguna
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    return view('portal');
})->middleware('auth')->name('dashboard');

// Modul Terproteksi Autentikasi & Role Authorization
Route::middleware(['auth'])->group(function () {

    // 1. Master Data
    Route::middleware(['role:Owner,Admin Penjualan,Admin Pembelian'])->group(function () {
        Route::apiResource('barang', BarangController::class);
    });

    Route::middleware(['role:Owner,Sales,Admin Penjualan,Admin Piutang'])->group(function () {
        Route::apiResource('pelanggan', PelangganController::class);
    });

    Route::middleware(['role:Owner,Admin Pembelian,Admin Hutang'])->group(function () {
        Route::apiResource('supplier', SupplierController::class);
    });

    Route::middleware(['role:Owner'])->group(function () {
        Route::apiResource('coa', CoaController::class);
    });

    // 2. Transaksi Pengadaan (Purchasing)
    Route::middleware(['role:Owner,Admin Pembelian'])->group(function () {
        Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
        Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');
        Route::get('/po/{no_po}', [PurchaseOrderController::class, 'show'])->name('po.show');
        Route::patch('/po/{no_po}/status', [PurchaseOrderController::class, 'updateStatus'])->name('po.updateStatus');

        Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
        Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');
        Route::get('/pembelian/{nota_pembelian}', [PembelianController::class, 'show'])->name('pembelian.show');
        Route::post('/pembelian/{nota_pembelian}/verify', [PembelianController::class, 'verify'])->name('pembelian.verify');

        Route::get('/retur-pembelian', [ReturPembelianController::class, 'index'])->name('retur-pembelian.index');
        Route::post('/retur-pembelian', [ReturPembelianController::class, 'store'])->name('retur-pembelian.store');
        Route::post('/retur-pembelian/{no_retur}/verify', [ReturPembelianController::class, 'verify'])->name('retur-pembelian.verify');
    });

    Route::middleware(['role:Owner,Admin Hutang,Kasir'])->group(function () {
        Route::get('/pembayaran-hutang', [PembayaranHutangController::class, 'index'])->name('pembayaran-hutang.index');
        Route::post('/pembayaran-hutang', [PembayaranHutangController::class, 'store'])->name('pembayaran-hutang.store');
        Route::post('/pembayaran-hutang/{no_pembayaran}/verify', [PembayaranHutangController::class, 'verify'])->name('pembayaran-hutang.verify');
    });

    // 3. Transaksi Penjualan (Sales)
    Route::middleware(['role:Owner,Sales,Admin Penjualan,Kasir'])->group(function () {
        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::post('/penjualan', [PenjualanController::class, 'store'])->name('penjualan.store');
        Route::get('/penjualan/{id_penjualan}', [PenjualanController::class, 'show'])->name('penjualan.show');
        Route::post('/penjualan/{id_penjualan}/verify', [PenjualanController::class, 'verify'])->name('penjualan.verify');

        Route::get('/retur-penjualan', [ReturPenjualanController::class, 'index'])->name('retur-penjualan.index');
        Route::post('/retur-penjualan', [ReturPenjualanController::class, 'store'])->name('retur-penjualan.store');
        Route::post('/retur-penjualan/{no_retur_penjualan}/verify', [ReturPenjualanController::class, 'verify'])->name('retur-penjualan.verify');
    });

    Route::middleware(['role:Owner,Admin Piutang,Kasir'])->group(function () {
        Route::get('/pelunasan-piutang', [PelunasanPiutangController::class, 'index'])->name('pelunasan-piutang.index');
        Route::post('/pelunasan-piutang', [PelunasanPiutangController::class, 'store'])->name('pelunasan-piutang.store');
        Route::post('/pelunasan-piutang/{no_pelunasan}/verify', [PelunasanPiutangController::class, 'verify'])->name('pelunasan-piutang.verify');
    });

    // 4. Beban Operasional
    Route::middleware(['role:Owner,Kasir'])->group(function () {
        Route::get('/beban', [BebanController::class, 'index'])->name('beban.index');
        Route::post('/beban', [BebanController::class, 'store'])->name('beban.store');
        Route::post('/beban/{id_beban}/verify', [BebanController::class, 'verify'])->name('beban.verify');
    });

    // 5. Cetak Dokumen PDF
    Route::get('/penjualan/{id}/pdf', [PdfController::class, 'fakturPenjualan'])->name('penjualan.pdf');
    Route::get('/pembelian/{nota}/pdf', [PdfController::class, 'notaPembelian'])->name('pembelian.pdf');
    Route::get('/retur-penjualan/{id}/pdf', [PdfController::class, 'returPenjualan'])->name('retur-penjualan.pdf');
    Route::get('/retur-pembelian/{id}/pdf', [PdfController::class, 'returPembelian'])->name('retur-pembelian.pdf');
    Route::get('/pelunasan-piutang/{id}/pdf', [PdfController::class, 'buktiPiutang'])->name('pelunasan-piutang.pdf');
    Route::get('/pembayaran-hutang/{id}/pdf', [PdfController::class, 'buktiHutang'])->name('pembayaran-hutang.pdf');
});
