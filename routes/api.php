<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\ForgotPasswordAPIController;
use App\Http\Controllers\API\Auth\ResetPasswordAPIController;

Route::post('/login', 'App\Http\Controllers\API\Auth\UserAuthController@login');
Route::post('/register', 'App\Http\Controllers\API\Auth\UserAuthController@register');
Route::post('/logout', 'App\Http\Controllers\API\Auth\UserAuthController@logout')->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/get-nota', 'App\Http\Controllers\API\Procedure\ProcedureController@getNotaPemesanan');

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
