<?php

use App\Helpers\MinioHelper;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview/{path}', function ($path) {
    // Kalau pakai spasi atau karakter khusus
    return MinioHelper::preview($path);
})->where('path', '.*'); // support path berisi slash (/)
