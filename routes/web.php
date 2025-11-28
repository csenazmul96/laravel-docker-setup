<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\TestController::class, 'index']);
