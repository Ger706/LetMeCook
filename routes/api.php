<?php

use App\Http\Controllers\IngredientController;
use App\Http\Controllers\RecipeController;
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

Route::prefix('recipe')->group(function () {
    Route::post('/create', [RecipeController::class, 'createRecipe']);
    Route::post('/all', [RecipeController::class, 'getRecipeList']);
    Route::get('/{recipeId}', [RecipeController::class, 'getRecipeDetail']);
    Route::post('/by-ingredient', [RecipeController::class, 'getRecipesByIngredient']);
    Route::get('/by-user/{userId}', [RecipeController::class, 'getRecipesByUser']);

    Route::group(['prefix' => 'favorite'], function () {
        Route::post('/add', [RecipeController::class, 'setRecipeAsFavorite']);
        Route::post('/remove/{favoriteRecipeId}', [RecipeController::class, 'deleteFavoriteRecipes']);
        Route::get('/{userId}', [RecipeController::class, 'getUserFavoriteRecipes']);
    });
});