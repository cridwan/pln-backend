<?php

use Illuminate\Support\Facades\Route;
use Spatie\RouteDiscovery\Discovery\Discover;


Route::get('/', function () {
    return view('welcome');
});


Route::prefix('api')->group(function () {
    Discover::controllers()->in(app_path('Http/Controllers'));
});
