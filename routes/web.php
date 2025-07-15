<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview/{path}', function ($path) {
    // Kalau pakai spasi atau karakter khusus
    $decodedPath = urldecode($path);

    // Cek apakah file ada
    if (!Storage::disk('s3')->exists($decodedPath)) {
        abort(404, 'File not found');
    }

    // Generate presigned URL berlaku 5 menit
    $url = Storage::disk('s3')->temporaryUrl(
        $decodedPath,
        now()->addMinutes(5)
    );

    return redirect($url);
})->where('path', '.*'); // support path berisi slash (/)
