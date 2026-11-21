<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JukirMappingController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LokasiParkirController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\RambuLokasiController;
use App\Http\Controllers\SheetConfigController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZonaParkirController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
});

Route::middleware('auth')->group(function () {
    Route::resource('dashboard', DashboardController::class);
    Route::resource('user', UserController::class);

    Route::prefix('user')->name('user.')->middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/',                  [UserController::class, 'index'])->name('index');
        Route::post('/',                 [UserController::class, 'store'])->name('store');
        Route::put('/{user}',            [UserController::class, 'update'])->name('update');
        Route::delete('/{user}',         [UserController::class, 'destroy'])->name('destroy');
        Route::post('/assign',           [UserController::class, 'assign'])->name('assign');
        Route::delete('/jukir/{jukir}',  [UserController::class, 'detach'])->name('detach');
    });

    Route::resource('config', SheetConfigController::class);
    Route::post('config/sync', [SheetConfigController::class, 'sync'])->name('config.sync');

    Route::resource('/monitoring', MonitoringController::class)->only(['index', 'show']);

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

    Route::prefix('lokasi-parkir')->name('lokasi-parkir.')->middleware('auth')->group(function () {
        Route::get('/',                          [LokasiParkirController::class, 'index'])->name('index');
        Route::post('/',                         [LokasiParkirController::class, 'store'])->name('store');
        Route::put('/{lokasiParkir}',            [LokasiParkirController::class, 'update'])->name('update');
        Route::delete('/{lokasiParkir}',         [LokasiParkirController::class, 'destroy'])->name('destroy');
    });

    Route::resource('zona-parkir', ZonaParkirController::class);
    Route::resource('rambu-lokasi', RambuLokasiController::class);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
