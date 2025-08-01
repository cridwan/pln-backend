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
        $uuid = \Illuminate\Support\Str::uuid();
        $storagePath = "{$path}/{$uuid}-{$fileName}";

        $stream = fopen('php://input', 'rb');

        try {
            $success = Storage::disk('s3')->writeStream($storagePath, $stream);
            fclose($stream);

            if (!$success) {
                return response()->json([
                    'message' => 'Gagal menyimpan file ke MinIO'
                ], 500);
            }

            $url = Storage::disk('s3')->url($storagePath);

            return response()->json([
                'filename' => $fileName,
                'path' => "preview/" . $storagePath,
                'url' => $url,
            ])->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => '*',
            ]);
        } catch (\Throwable $e) {
            fclose($stream);

            return response()->json([
                'message' => 'Gagal upload file ke MinIO',
                'error' => $e->getMessage()
            ], 500)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => '*',
            ]);
        }
    }
}
