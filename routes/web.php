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
Route::get('/dashboard/pdf/{id}', [DashboardController::class, 'generatePdf'])->name('dashboard.pdf');
Route::get('/dashboard/word/{id}', [DashboardController::class, 'generateWord'])->name('dashboard.word');

// Admin Management Routes (CRUD Sesi 1-5 & Posisi)
Route::get('/dashboard/posisi', [DashboardController::class, 'listPosisi'])->name('dashboard.posisi');
Route::post('/dashboard/posisi', [DashboardController::class, 'storePosisi'])->name('dashboard.posisi.store');
Route::post('/dashboard/posisi/{id}/update', [DashboardController::class, 'updatePosisi'])->name('dashboard.posisi.update');
Route::post('/dashboard/posisi/{id}/delete', [DashboardController::class, 'deletePosisi'])->name('dashboard.posisi.delete');

Route::get('/dashboard/sesi1', [DashboardController::class, 'listSesi1'])->name('dashboard.sesi1');
Route::post('/dashboard/sesi1', [DashboardController::class, 'storeSesi1'])->name('dashboard.sesi1.store');
Route::post('/dashboard/sesi1/{id}/update', [DashboardController::class, 'updateSesi1'])->name('dashboard.sesi1.update');
Route::post('/dashboard/sesi1/{id}/delete', [DashboardController::class, 'deleteSesi1'])->name('dashboard.sesi1.delete');

Route::get('/dashboard/sesi2', [DashboardController::class, 'listSesi2'])->name('dashboard.sesi2');
Route::post('/dashboard/sesi2', [DashboardController::class, 'storeSesi2'])->name('dashboard.sesi2.store');
Route::post('/dashboard/sesi2/{id}/update', [DashboardController::class, 'updateSesi2'])->name('dashboard.sesi2.update');
Route::post('/dashboard/sesi2/{id}/delete', [DashboardController::class, 'deleteSesi2'])->name('dashboard.sesi2.delete');

Route::get('/dashboard/sesi3', [DashboardController::class, 'listSesi3'])->name('dashboard.sesi3');
Route::post('/dashboard/sesi3', [DashboardController::class, 'storeSesi3'])->name('dashboard.sesi3.store');
Route::post('/dashboard/sesi3/{id}/update', [DashboardController::class, 'updateSesi3'])->name('dashboard.sesi3.update');
Route::post('/dashboard/sesi3/{id}/delete', [DashboardController::class, 'deleteSesi3'])->name('dashboard.sesi3.delete');

Route::get('/dashboard/sesi4', [DashboardController::class, 'listSesi4'])->name('dashboard.sesi4');
Route::post('/dashboard/sesi4/update', [DashboardController::class, 'updateSesi4'])->name('dashboard.sesi4.update');

Route::get('/dashboard/sesi5', [DashboardController::class, 'listSesi5'])->name('dashboard.sesi5');
Route::post('/dashboard/sesi5', [DashboardController::class, 'storeSesi5'])->name('dashboard.sesi5.store');
Route::post('/dashboard/sesi5/{id}/update', [DashboardController::class, 'updateSesi5'])->name('dashboard.sesi5.update');
Route::post('/dashboard/sesi5/{id}/delete', [DashboardController::class, 'deleteSesi5'])->name('dashboard.sesi5.delete');

// Assessment Rubric Routes
Route::get('/dashboard/rubrik', [DashboardController::class, 'listRubrik'])->name('dashboard.rubrik');
Route::post('/dashboard/rubrik/{id}/update', [DashboardController::class, 'updateRubrik'])->name('dashboard.rubrik.update');

// Candidate Result Edit Routes
Route::get('/dashboard/peserta/{id}/edit', [DashboardController::class, 'editPeserta'])->name('dashboard.peserta.edit');
Route::post('/dashboard/peserta/{id}/update', [DashboardController::class, 'updatePeserta'])->name('dashboard.peserta.update');

// Time Settings Routes
Route::post('/dashboard/durasi/{sesi}/update', [DashboardController::class, 'updateDurasiSesi'])->name('dashboard.update_durasi');

Route::get('/ujian/diskualifikasi', [App\Http\Controllers\UjianController::class, 'diskualifikasi'])->name('ujian.diskualifikasi');