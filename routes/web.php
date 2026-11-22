<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\Owner;
use Illuminate\Support\Facades\Route;

Route::get('/', [GarageController::class, 'index'])->name('garages.index');
Route::get('/garasi/{garage}', [GarageController::class, 'show'])->name('garages.show');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

    // file privat: otorisasi dicek di controller (penyewa terkait atau pemilik garasi)
    Route::get('/berkas/bukti/{booking}', [BookingController::class, 'proof'])->name('files.proof');
    Route::get('/berkas/surat/{document}', [DocumentFileController::class, 'show'])->name('files.document');

    Route::middleware('role:tenant')->group(function () {
        Route::get('/pengajuan', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('/garasi/{garage}/pesan', [BookingController::class, 'store'])->name('bookings.store');
        Route::post('/pengajuan/{booking}/batal', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/pengajuan/{booking}/bukti', [BookingController::class, 'uploadProof'])->name('bookings.proof');
    });

    Route::middleware('role:owner')->prefix('pemilik')->name('owner.')->group(function () {
        Route::get('/', Owner\DashboardController::class)->name('dashboard');

        Route::resource('garages', Owner\GarageController::class)->except(['show', 'destroy']);
        Route::post('/garages/{garage}/slots', [Owner\SlotController::class, 'store'])->name('slots.store');
        Route::patch('/slots/{slot}/toggle', [Owner\SlotController::class, 'toggle'])->name('slots.toggle');

        Route::get('/pengajuan', [Owner\BookingController::class, 'index'])->name('bookings.index');
        Route::post('/pengajuan/{booking}/terima', [Owner\BookingController::class, 'accept'])->name('bookings.accept');
        Route::post('/pengajuan/{booking}/tolak', [Owner\BookingController::class, 'reject'])->name('bookings.reject');
        Route::post('/pengajuan/{booking}/bayar', [Owner\BookingController::class, 'pay'])->name('bookings.pay');
        Route::post('/pengajuan/{booking}/selesai', [Owner\BookingController::class, 'finish'])->name('bookings.finish');
        Route::post('/pengajuan/{booking}/putus', [Owner\BookingController::class, 'terminate'])->name('bookings.terminate');

        Route::get('/surat', [Owner\DocumentController::class, 'index'])->name('documents.index');
        Route::post('/surat', [Owner\DocumentController::class, 'store'])->name('documents.store');
    });
});
