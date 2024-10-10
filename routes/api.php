<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::group(['prefix' => 'user'], function () {
    Route::post('/create', ['as' => 'user.create', 'uses' => 'UserController@create']);
});
