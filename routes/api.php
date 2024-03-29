<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', 'App\Http\Controllers\API\Auth\UserAuthController@login');
Route::post('/register', 'App\Http\Controllers\API\Auth\UserAuthController@register');
Route::post('/logout', 'App\Http\Controllers\API\Auth\UserAuthController@logout')->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/get-nota', 'App\Http\Controllers\API\Procedure\ProcedureController@getNotaPemesanan');
