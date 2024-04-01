<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\UserAuthController;
use App\Http\Controllers\API\Auth\ForgotPasswordAPIController;
use App\Http\Controllers\API\Auth\ResetPasswordAPIController;
use App\Http\Controllers\API\Data\ProdukController;

use App\Http\Controllers\API\Procedure\ProcedureController;

Route::post('/login', [UserAuthController::class, 'login'])->name('login');
Route::post('/register',  [UserAuthController::class, 'register'])->name('register');
Route::post('/logout',  [UserAuthController::class, 'logout'])->name('logout')
->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'ability:admin']);

Route::get('/get-nota',  [ProcedureController::class, 'getNotaPemesanan'])->name('get-nota');
Route::post('password/email', [ForgotPasswordAPIController::class, 'sendResetLinkEmail'])->name('password.email');
/*
    Post dari front-end
    api/password/email?email={email}
*/

Route::post('password/reset', [ResetPasswordAPIController::class, 'reset'])->name('password.update');
/*
    Post dari front-end
    api/password/reset?token={token}&password={pass}&password_confirmation={pass_conf}
*/

Route::apiResource('produk', ProdukController::class);
