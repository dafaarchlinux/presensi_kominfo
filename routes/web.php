<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthPnsController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\FaceEnrollmentController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\Admin\AdminPresensiController;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
| Arahkan ke login PNS (AMAN)
*/
Route::get('/', function () {
    return redirect()->route('pns.login');
});

/*
|--------------------------------------------------------------------------
| AUTH USER PNS
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthPnsController::class, 'showLogin'])
    ->name('pns.login');

Route::post('/login', [AuthPnsController::class, 'login'])
    ->name('pns.login.submit');

Route::post('/logout', [AuthPnsController::class, 'logout'])
    ->name('pns.logout');

/*
|--------------------------------------------------------------------------
| USER PNS AREA (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth:pns'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | HALAMAN PRESENSI WAJAH PNS
    |--------------------------------------------------------------------------
    */
    Route::get('/pns/presensi-wajah', [
        PresensiController::class,
        'halamanPresensiPns'
    ])->name('pns.presensi.wajah');

    /*
    |--------------------------------------------------------------------------
    | ENDPOINT PRESENSI (FACE + GPS)
    |--------------------------------------------------------------------------
    */
    Route::post('/pns/presensi', [
        PresensiController::class,
        'presensi'
    ])->name('pns.presensi');
});

/*
|--------------------------------------------------------------------------
| ROUTES SYSTEM / FACE API
|--------------------------------------------------------------------------
*/
Route::middleware('web')->group(function () {

    // FACE ENROLLMENT (ADMIN)
    Route::post('/face/enroll', [
        FaceEnrollmentController::class,
        'store',
    ])->name('face.enroll');

    // FACE EMBEDDINGS (ADMIN / SYSTEM)
    Route::get('/face/embeddings', [
        FaceRecognitionController::class,
        'embeddings',
    ])->name('face.embeddings');
});

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])
    ->prefix('admin')
    ->group(function () {

        Route::post('/presensi', [
            AdminPresensiController::class,
            'presensi'
        ])->name('admin.presensi');
    });