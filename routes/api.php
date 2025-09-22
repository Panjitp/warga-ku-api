<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WargaController;
use App\Http\Controllers\Api\IuranController;
use App\Http\Controllers\Api\TagihanController;
use App\Http\Controllers\Api\KeuanganController; 
use App\Http\Controllers\Api\VerifikasiController; 
use App\Http\Controllers\Api\PengumumanController;
use App\Http\Controllers\Api\SaranController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\KasController;
use App\Http\Controllers\Api\Admin\RtAccountController;
use App\Http\Controllers\Api\Admin\RtManagementController; 
use App\Http\Controllers\Api\ProfilController; 
use App\Http\Controllers\Api\PerubahanDataController;
use App\Http\Controllers\Api\RtController; // <-- TAMBAHKAN INI
use App\Http\Controllers\Api\DirektoriController;
use App\Http\Controllers\Api\PanicButtonController; // <-- TAMBAHKAN INI



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/profil/verifikasi-email/{token}', [ProfilController::class, 'verifikasiEmailBaru'])
     ->name('verification.verify_new_email');

// Endpoint yang dilindungi (butuh token)
Route::middleware('auth:sanctum')->group(function () {

    // routes/api.php
    Route::get('/me', function () {
        return auth()->user();
    });

    Route::post('/profil/toggle-direktori', [ProfilController::class, 'toggleDirektori']);
    

    Route::get('/rt/{rt}', [RtController::class, 'show']);

    Route::post('/profil/request-ubah-email', [ProfilController::class, 'requestUbahEmail']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('warga', WargaController::class)->middleware('can:is-pengurus');

    // --- Rute untuk Manajemen Iuran (Hanya RW) ---
    Route::apiResource('iuran', IuranController::class)->middleware('can:is-pengurus-rw');

    // --- Rute untuk Manajemen Tagihan (Hanya RT) ---
    // routes/api.php
    Route::post('/profil/ajukan-perubahan', [PerubahanDataController::class, 'store'])
        ->middleware('can:is-warga');

    // Pengurus RT melihat daftar & memproses
    Route::get('/pengurus/perubahan-data', [PerubahanDataController::class, 'index'])
        ->middleware('can:is-pengurus-rt');
    Route::post('/pengurus/perubahan-data/{permintaan}', [PerubahanDataController::class, 'proses'])
        ->middleware('can:is-pengurus-rt');
    // ...

    Route::middleware('can:is-pengurus-rt')->group(function () {
        // Rute BARU untuk melihat iuran yang bisa ditagih
        Route::get('tagihan/available-iuran', [TagihanController::class, 'getAvailableIuran']);
        
        // Rute LAMA yang sekarang logikanya sudah dimodifikasi
        Route::post('tagihan/generate', [TagihanController::class, 'generate']);
        
        // Rute untuk melihat daftar tagihan (tidak berubah)
        Route::get('tagihan', [TagihanController::class, 'index']);
    });

    Route::middleware('can:is-warga')->group(function () {
        Route::get('/keuangan/tagihan', [KeuanganController::class, 'index']);
        Route::post('/keuangan/tagihan/{tagihan}/bayar', [KeuanganController::class, 'bayar']);
    });

    Route::get('/pengumuman', [PengumumanController::class, 'index'])->middleware('can:is-warga');

    // Pengurus membuat, melihat, update, hapus pengumuman
    Route::apiResource('pengurus/pengumuman', PengumumanController::class)
        ->middleware('can:is-pengurus');
    
    Route::post('/saran', [SaranController::class, 'store'])->middleware('can:is-warga');
    Route::get('/saran/riwayat', [SaranController::class, 'riwayat'])->middleware('can:is-warga');

    // Pengurus melihat dan mengelola saran yang masuk
    Route::get('/pengurus/saran', [SaranController::class, 'index'])->middleware('can:is-pengurus');
    Route::patch('/pengurus/saran/{saran}', [SaranController::class, 'updateStatus'])->middleware('can:is-pengurus');

    Route::get('/laporan/iuran/excel', [LaporanController::class, 'exportIuranExcel'])
        ->middleware('can:is-pengurus');

    Route::get('/laporan/keuangan/pdf', [LaporanController::class, 'exportKeuanganPdf']) // <-- Tambahkan ini
        ->middleware('can:is-pengurus');

    Route::post('/kas/pengeluaran', [KasController::class, 'catatPengeluaran'])
        ->middleware('can:is-pengurus-rt');

    Route::middleware('can:is-pengurus-rw')->group(function() {
        Route::apiResource('admin/akun-rt', RtAccountController::class);
        Route::apiResource('admin/rt', RtManagementController::class); // <-- TAMBAHKAN INI
    });

    // --- Rute untuk Verifikasi Pembayaran (Hanya RT) ---
    Route::middleware('can:is-pengurus-rt')->group(function() {
        Route::get('/verifikasi/pembayaran', [VerifikasiController::class, 'index']);
        Route::post('/verifikasi/pembayaran/{pembayaran}', [VerifikasiController::class, 'verifikasi']);
    });

    Route::get('/warga/detail/{user}', [WargaController::class, 'detail']);

    Route::post('/profil/ubah-password', [ProfilController::class, 'ubahPassword']);

    Route::post('/panic-button', [PanicButtonController::class, 'trigger'])
        ->middleware('can:is-warga');

    Route::get('/direktori-warga', [DirektoriController::class, 'index']);
});
