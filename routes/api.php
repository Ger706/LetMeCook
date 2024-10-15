<?php

use App\Http\Controllers\IngredientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;



Route::prefix('user')->group(function () {
    Route::post('/create', [UserController::class, 'createUser']);
    Route::get('/{userId}', [UserController::class, 'getUser']);
    Route::post('/login', [UserController::class, 'login']);
});

Route::prefix('ingredient')->group(function () {
    Route::post('/list', [IngredientController::class, 'getIngredientList']);
});
