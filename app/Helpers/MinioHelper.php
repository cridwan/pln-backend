<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
}
