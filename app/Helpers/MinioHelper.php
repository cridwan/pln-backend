<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Illuminate\Support\Str;

class MinioHelper
{
    public static function upload(UploadedFile $file)
    {
        Log::info('Start upload');

        if (!$file) {
            return response()->json(['error' => 'File tidak ditemukan'], 400);
        }

        $bucket = env('AWS_BUCKET');
        $filename = $file->getClientOriginalName();
        $contentType = $file->getClientMimeType();
        $key = 'documents/' . Str::uuid() . "-{$filename}";

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        try {
            $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => fopen($file->getRealPath(), 'rb'),
                'ContentType' => $contentType,
                'ACL' => 'public-read', // optional
            ]);

            return [
                'message' => 'Upload berhasil',
                'path' => 'preview/' . $key,
                'filename' => $filename,
                'hash_name' => $filename,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function uploadStream(Request $request)
    {
        Log::info('start stream');
        $bucket = env('AWS_BUCKET');
        $filename = urldecode($request->header('X-Filename') ?? 'uploaded.bin');
        $contentType = $request->header('Content-Type', 'application/octet-stream');
        $key = 'uploads/' . Str::uuid() . "-{$filename}";

        $stream = fopen('php://input', 'rb');
        Log::info('upload ke minio stream');
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        try {
            $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => $stream,
                'ContentType' => $contentType,
                'ACL'    => 'public-read', // opsional, tergantung kebutuhan
            ]);
            Log::info('upload selesai stream');
            fclose($stream);
            Log::info('close stream');

            return [
                'message' => 'Upload berhasil',
                'key' => $key,
                'filename' => $filename,
                'hash_name' => $filename,
                'size' => $request->header('X-Filesize', 'video/mp4'),
                'mime_type' => $request->header('X-Filemimetype', 0),
            ];
        } catch (\Exception $e) {
            Log::info('error stream');
            fclose($stream);
            throw $e;
        }
    }
}
