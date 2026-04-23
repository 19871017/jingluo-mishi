<?php

use think\facade\Route;

Route::group('api', function () {

    Route::get('home', 'HomeController/index');

    Route::get('scripts/search', 'ScriptController/search');
    Route::get('scripts/:id', 'ScriptController/detail')->pattern(['id' => '\d+']);

    Route::get('categories', 'CategoryController/list');
    Route::get('categories/:id/scripts', 'CategoryController/scripts')->pattern(['id' => '\d+']);

    Route::get('brands', 'BrandController/list');
    Route::get('brands/:id', 'BrandController/detail')->pattern(['id' => '\d+']);

    Route::get('market', 'MarketController/list');

    Route::group('user', function () {
        Route::post('login', 'UserController/login');
        Route::get('profile', 'UserController/profile')->middleware(\app\middleware\AuthMiddleware::class);
        Route::put('profile', 'UserController/update')->middleware(\app\middleware\AuthMiddleware::class);
        Route::get('favorites', 'UserController/favorites')->middleware(\app\middleware\AuthMiddleware::class);
        Route::get('follows', 'UserController/follows')->middleware(\app\middleware\AuthMiddleware::class);
    });

    Route::group('scripts/:id', function () {
        Route::post('like', 'ScriptController/like')->middleware(\app\middleware\AuthMiddleware::class);
        Route::post('unlike', 'ScriptController/unlike')->middleware(\app\middleware\AuthMiddleware::class);
        Route::post('collect', 'ScriptController/collect')->middleware(\app\middleware\AuthMiddleware::class);
    })->pattern(['id' => '\d+']);

    Route::group('brands/:id', function () {
        Route::post('follow', 'BrandController/follow')->middleware(\app\middleware\AuthMiddleware::class);
        Route::post('unfollow', 'BrandController/unfollow')->middleware(\app\middleware\AuthMiddleware::class);
    })->pattern(['id' => '\d+']);

    Route::post('market/listings', 'MarketController/create')->middleware(\app\middleware\AuthMiddleware::class);
    Route::get('market/:id', 'MarketController/detail')->pattern(['id' => '\d+']);

})->middleware(\app\middleware\CorsMiddleware::class);
