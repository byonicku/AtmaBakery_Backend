<?php

use App\Http\Controllers\API\Data\UserController;
use Illuminate\Http\Request;
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

Route::controller(ProdukController::class)
         ->group(function () {
                Route::post('/produk/gambar/{id}', 'storeGambar')->name('add-gambar')->middleware(['auth:sanctum', 'ability:admin,owner']);
         })->name('produk');

Route::controller(ProcedureController::class)
       ->middleware(['auth:sanctum', 'ability:mo,owner'])
       ->group(function () {
            Route::get('/get-nota', 'getNotaPemesanan')->name('get-nota');

       })->name('laporan');

