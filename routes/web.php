<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\UserAuthController;


Route::get('/', function () {
    return view('welcome');
});
