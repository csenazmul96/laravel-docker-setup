<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::get('/', [\App\Http\Controllers\TestController::class, 'index']);
Route::prefix('admin')->group(function () {
    include 'api/admin.php';
});
