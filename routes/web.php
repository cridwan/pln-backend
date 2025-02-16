<?php

use Illuminate\Support\Facades\Route;
use Spatie\RouteDiscovery\Discovery\Discover;


Route::get('/', function () {
    return view('welcome');
});
