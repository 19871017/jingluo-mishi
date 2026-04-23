<?php

use think\facade\Route;

Route::group('api/admin', function () {

    Route::post('login', 'Admin/AuthController/login');
    Route::post('logout', 'Admin/AuthController/logout')->middleware(\app\middleware\AdminMiddleware::class);
    Route::get('profile', 'Admin/AuthController/profile')->middleware(\app\middleware\AdminMiddleware::class);
    Route::put('password', 'Admin/AuthController/updatePassword')->middleware(\app\middleware\AdminMiddleware::class);

    Route::get('admins', 'Admin/AdminController/list')->middleware(\app\middleware\AdminMiddleware::class);
    Route::post('admins', 'Admin/AdminController/create')->middleware(\app\middleware\AdminMiddleware::class);
    Route::put('admins/:id', 'Admin/AdminController/update')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);
    Route::delete('admins/:id', 'Admin/AdminController/delete')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);

    Route::get('categories', 'Admin/CategoryController/list');
    Route::post('categories', 'Admin/CategoryController/create')->middleware(\app\middleware\AdminMiddleware::class);
    Route::put('categories/:id', 'Admin/CategoryController/update')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);
    Route::delete('categories/:id', 'Admin/CategoryController/delete')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);

    Route::get('brands', 'Admin/BrandController/list');
    Route::get('brands/:id', 'Admin/BrandController/detail')->pattern(['id' => '\d+']);
    Route::put('brands/:id/audit', 'Admin/BrandController/audit')->pattern(['id' => '\d+']);

    Route::get('scripts', 'Admin/ScriptController/list');
    Route::post('scripts', 'Admin/ScriptController/create')->middleware(\app\middleware\AdminMiddleware::class);
    Route::put('scripts/:id', 'Admin/ScriptController/update')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);
    Route::delete('scripts/:id', 'Admin/ScriptController/delete')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);
    Route::put('scripts/:id/audit', 'Admin/ScriptController/audit')->pattern(['id' => '\d+']);
    Route::put('scripts/:id/restore', 'Admin/ScriptController/restore')->pattern(['id' => '\d+']);
    Route::delete('scripts/:id/permanent', 'Admin/ScriptController/permanentDelete')->pattern(['id' => '\d+']);

    Route::get('market/listings', 'Admin/MarketController/listings');
    Route::put('market/listings/:id/audit', 'Admin/MarketController/audit')->pattern(['id' => '\d+']);
    Route::delete('market/listings/:id', 'Admin/MarketController/delete')->pattern(['id' => '\d+']);
    Route::put('market/listings/:id/featured', 'Admin/MarketController/setFeatured')->pattern(['id' => '\d+']);

    Route::get('stats/overview', 'Admin/StatsController/overview');
    Route::get('stats/trend', 'Admin/StatsController/trend');

    Route::get('home/banners', 'Admin/HomeContentController/banners');
    Route::post('home/banners', 'Admin/HomeContentController/createBanner')->middleware(\app\middleware\AdminMiddleware::class);
    Route::put('home/banners/:id', 'Admin/HomeContentController/updateBanner')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);
    Route::delete('home/banners/:id', 'Admin/HomeContentController/deleteBanner')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);

    Route::get('home/ads', 'Admin/HomeContentController/ads');
    Route::post('home/ads', 'Admin/HomeContentController/createAd')->middleware(\app\middleware\AdminMiddleware::class);
    Route::put('home/ads/:id', 'Admin/HomeContentController/updateAd')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);
    Route::delete('home/ads/:id', 'Admin/HomeContentController/deleteAd')->middleware(\app\middleware\AdminMiddleware::class)->pattern(['id' => '\d+']);

})->middleware(\app\middleware\CorsMiddleware::class);
