<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:admin')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::apiResource('banners', BannerController::class);
});

