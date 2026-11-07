<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstitusiController;
use App\Http\Controllers\LogWebhookController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PeriodeAkademikController;
use App\Http\Controllers\TagihanUktController;
use App\Http\Controllers\TransaksiPembayaranController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'login')->name('login');
    Route::get('/login', 'login');
    Route::post('/loginproses', 'loginproses')->name('loginproses');
    Route::match(['get', 'post'], '/logout', 'logout')->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class)->names('users');

    // Data Master
    Route::resource('institusi', InstitusiController::class)->names('institusis');
    Route::resource('mahasiswa', MahasiswaController::class)->names('mahasiswas');
    Route::resource('periode-akademik', PeriodeAkademikController::class)->names('periode-akademiks');

    // Transaksi & UKT
    Route::resource('tagihan-ukt', TagihanUktController::class)->names('tagihan-ukts');
    Route::resource('transaksi-pembayaran', TransaksiPembayaranController::class)->names('transaksi-pembayarans');

    // Log Webhook
    Route::resource('log-webhook', LogWebhookController::class)->names('log-webhooks');
});
