<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Helpers\MinioHelper;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\DocumentRequest;
use App\Models\Storage\Document;
use App\Traits\HasList;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\File;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group('Document')]
class DocumentController extends Controller implements HasMiddleware
{
    use HasList;
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list']),
        ];
    }

    /**
     * store document
     */
    #[Route('POST')]
    public function store(DocumentRequest $request)
    {
        $uploaded = MinioHelper::upload($request->file('document'));

        return Document::create($request->merge([
            'document_original_name' => $uploaded['filename'],
            'document_name' => $uploaded['hash_name'],
            'document_link' => $uploaded['path'],
            'document_size' => $uploaded['size'],
            'document_mime_type' => $uploaded['mime_type'],
        ])->all());
    }

    /**
     * store stream document
     */
    #[Route('POST', uri: 'stream')]
    public function stream(Request $request)
    {
        $uploaded = MinioHelper::uploadStream($request);

        return Document::create([
            'document_original_name' => $uploaded['filename'],
            'document_name' => $uploaded['filename'],
            'document_link' => $uploaded['path'],
            'document_size' => $request->header('X-filesize'),
            'document_mime_type' => $request->header('X-filemimetype'),
            'document_type' => $request->header('X-modeltype'),
            'document_uuid' => $request->header('X-modeluuid'),
        ]);
    }

    /**
     * delete document
     */
    #[Route(method: 'DELETE', uri: 'delete/multi')]
    public function destroys(Request $request)
    {
        $documents = Document::whereIn('uuid', $request->ids)->get();

        foreach ($documents as $document) {
            if (File::exists($document->document_link)) {
                File::delete($document->document_link);
            }
            Document::where('uuid', $document->uuid)->delete();
        }

        return ["message" => "Document deleted successfully"];
    }
}
