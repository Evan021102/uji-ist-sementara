<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UjianController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Localization Route
Route::get('/lang/{locale}', [\App\Http\Controllers\LanguageController::class, 'switchLanguage'])->name('lang.switch');

// Candidate Exam Routes
Route::get('/', [UjianController::class, 'index'])->name('ujian.index');
Route::post('/ujian/start', [UjianController::class, 'start'])->name('ujian.start');
Route::get('/ujian/petunjuk/{sesi}', [UjianController::class, 'petunjuk'])->name('ujian.petunjuk');
Route::get('/ujian/sesi/{sesi}', [UjianController::class, 'showSesi'])->name('ujian.sesi');
Route::post('/ujian/sesi/{sesi}', [UjianController::class, 'submitSesi'])->name('ujian.submit');
Route::get('/ujian/simpan', [UjianController::class, 'simpan'])->name('ujian.simpan');

// Dashboard Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Management Routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::post('/dashboard/pin', [DashboardController::class, 'updatePin'])->name('dashboard.pin');
Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

Route::get('/ujian/diskualifikasi', [App\Http\Controllers\UjianController::class, 'diskualifikasi'])->name('ujian.diskualifikasi');