<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\RouteDiscovery\Discovery\Discover;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group([], function () {
    Discover::controllers()->in(app_path('Http/Controllers'));
});
