<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\UserAuthController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify/{verify_key}', [App\Http\Controllers\API\Auth\UserAuthController::class, 'verify'])->name('verify');

Route::get('/verify/failed', function () {
    return view('FailedVerify');
});

Route::get('/verify/success', function () {
    return view('SuccessVerify');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
