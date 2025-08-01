<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Str;

class MinioHelper
{
    public static function upload(UploadedFile $file)
    {
        $filename = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        $path = 'documents/' . $file->hashName();
        Storage::disk('s3')->put($path, $file->get());

        $url = Storage::disk('s3')->url($path);

        return [
            'filename' => $filename,
            'hash_name' => $file->hashName(),
            'path' => 'preview/' . $path,
            'mime_type' => $mimeType,
            'size' => $size,
            'url' => $url,
        ];
    }

    public static function stream(Request $request, string $path = 'vidoes')
    {
        $fileName = urldecode($request->header('X-Filename') ?? 'uploaded.bin');

        $stream = fopen('php://input', 'rb');
        $storagePath = "{$path}/" . \Illuminate\Support\Str::uuid() . "-{$fileName}";

        Storage::disk('s3')->writeStream($storagePath, $stream);

        fclose($stream);

        // Ambil size dari Storage
        $url = Storage::disk('s3')->url($storagePath);

        return [
            'filename' =>  $fileName,
            'path' => "preview/" . $storagePath,
            'url' => $url,
        ];
    }
}
