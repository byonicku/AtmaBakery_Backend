<?php

use App\Http\Controllers\API\Data\GambarController;
use App\Http\Controllers\API\Data\KaryawanController;
use App\Http\Controllers\API\Data\PenitipController;
use App\Http\Controllers\API\Data\ResepController;
use App\Http\Controllers\API\Data\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\UserAuthController;
use App\Http\Controllers\API\Auth\ForgotPasswordAPIController;
use App\Http\Controllers\API\Auth\ResetPasswordAPIController;
use App\Http\Controllers\API\Data\ProdukController;
use App\Http\Controllers\API\Procedure\ProcedureController;

Route::controller(UserAuthController::class)
       ->group(function () {
        Route::post('/login', 'login')->name('login');
        Route::post('/register', 'register')->name('register');
        Route::post('/logout',  'logout')->name('logout')->middleware('auth:sanctum');
        Route::post('/verify/{verify_key}', 'verify')->name('verify');
        Route::post('/password/verify', 'verifyPassword')->name('verify-password');
       })->name('authentications');

Route::controller(UserController::class)
       ->group(function () {
        Route::get('/users/self', 'showSelf')->name('users')->middleware('auth:sanctum');
        Route::get('/users', 'index')->name('users')->middleware(['auth:sanctum', 'ability:admin,owner']);
        Route::get('/users/{id}', 'show')->name('users')->middleware(['auth:sanctum', 'ability:admin,owner']);
        Route::put('/users/{id}', 'update')->name('users')->middleware(['auth:sanctum', 'ability:user,admin,owner']);
        Route::delete('/users/{id}', 'destroy')->name('users')->middleware(['auth:sanctum', 'ability:admin,owner']);
       })->name('users');

/*
    Post dari front-end
    api/password/email?email={email}
*/
Route::post('password/email', [ForgotPasswordAPIController::class, 'sendResetLinkEmail'])
       ->name('sent-reset-link-email');

/*
    Post dari front-end
    api/password/reset?token={token}&password={pass}&password_confirmation={pass_conf}
*/
Route::post('password/reset', [ResetPasswordAPIController::class, 'reset'])
       ->name('password-reset');

Route::apiResource('produk', ProdukController::class)
       ->middleware(['auth:sanctum', 'ability:admin,owner']);

Route::controller(GambarController::class)
       ->middleware(['auth:sanctum', 'ability:admin,owner'])
       ->group(function () {
            Route::apiResource('gambar', GambarController::class, ['except' => ['update']]);
            Route::put('/gambar', 'update')->name('gambar.update');
            Route::get('/gambar/produk/{id}', 'showProduk')->name('gambar.produk');
            Route::get('/gambar/hampers/{id}', 'showHampers')->name('gambar.hampers');
       })->name('gambar');

Route::controller(ProcedureController::class)
       ->middleware(['auth:sanctum', 'ability:mo,owner'])
       ->group(function () {
            Route::post('/get-nota', 'getNotaPemesanan')->name('get-nota');

       })->name('laporan');

Route::controller(ResepController::class)
       ->middleware(['auth:sanctum', 'ability:admin,owner'])
       ->group(function () {
            Route::apiResource('resep', ResepController::class, ['except' => ['destroy', 'update']]);
            Route::put('/resep', 'update')->name('resep.update');
            Route::delete('/resep', 'destroy')->name('resep.destroy');
            Route::delete('/resep/all/{id_produk}', 'destroyAll')->name('resep.destroy-all');
       })->name('resep');

Route::apiResource('karyawan', KaryawanController::class)
        ->middleware(['auth:sanctum', 'ability:mo,owner']);

Route::apiResource('penitip', PenitipController::class)
        ->middleware(['auth:sanctum', 'ability:mo,owner']);
