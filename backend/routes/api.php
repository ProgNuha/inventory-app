<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/options/years', [OptionController::class, 'years']);
    Route::apiResource('barang-masuk', BarangMasukController::class);
    Route::get('/options/categories', [OptionController::class, 'categories']);
    Route::get('/options/sub-categories', [OptionController::class, 'subCategories']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('users', UserController::class)->except('show');
    Route::patch('users/{user}/lock', [UserController::class, 'toggleLock']);
    Route::get('/options/operators', [OptionController::class, 'operators']);
    Route::apiResource('categories', CategoryController::class)->except('show');
    Route::apiResource('sub-categories', SubCategoryController::class)->except('show');
});
