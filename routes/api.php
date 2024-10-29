<?php

use App\Http\Controllers\ExpertController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForumCommentController;



Route::prefix('user')->group(function () {
    Route::post('/register', [UserController::class, 'createUser']);
    Route::get('/{userId}', [UserController::class, 'getUser']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/set-premium/{userId}', [UserController::class, 'setUserAsPremium']);
});

Route::prefix('ingredient')->group(function () {
    Route::post('/list', [IngredientController::class, 'getIngredientList']);
    Route::post('/category-list', [IngredientController::class, 'getIngredientCategoryList']);
});

Route::prefix('recipe')->group(function () {
    Route::post('/create', [RecipeController::class, 'createRecipe']);
    Route::post('/all', [RecipeController::class, 'getRecipeList']);
    Route::post('/detail', [RecipeController::class, 'getRecipeDetail']);
    Route::post('/by-ingredient', [RecipeController::class, 'getRecipesByIngredient']);
    Route::get('/by-user/{userId}', [RecipeController::class, 'getRecipesByUser']);
    Route::get('/get-popular', [RecipeController::class, 'getPopularRecipes']);
    Route::get('/get-recommended', [RecipeController::class, 'getRecommendedRecipes']);

    Route::group(['prefix' => 'favorite'], function () {
        Route::post('/add', [RecipeController::class, 'setRecipeAsFavorite']);
        Route::post('/remove/{favoriteRecipeId}', [RecipeController::class, 'deleteFavoriteRecipes']);
        Route::get('/{userId}', [RecipeController::class, 'getUserFavoriteRecipes']);
    });
});

Route::prefix('forum') ->group(function() {
    Route::post('/',[ForumCommentController::class, 'commentForum']);
    Route::post('/get-comment',[ForumCommentController::class, 'getCommentsForRecipe']);
    Route::post('/like-dislike',[ForumCommentController::class, 'likeDislikeForum']);
});

Route::prefix('expert')->group(function () {
    Route::post('/create', [ExpertController::class, 'createExpert']);
    Route::get('/{expertId}', [ExpertController::class, 'getExpertDetail']);
    Route::post('/get-all', [ExpertController::class, 'getAllExperts']);
});