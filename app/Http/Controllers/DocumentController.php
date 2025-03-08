<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\DocumentRequest;
use App\Models\Transaction\Document;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group('Document')]
class DocumentController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
        ];
    }

    #[Route('POST')]
    public function store(DocumentRequest $request)
    {
        $document = $request->file('document');
        $documentFileName = $document->hashName();
        $documentSize = $document->getSize();
        $documentMimeType = $document->getMimeType();
        $documentLink = $document->storeAs('documents', $documentFileName);

        return Document::create($request->merge([
            'document_original_name' => $document->getClientOriginalName(),
            'document_name' => $documentFileName,
            'document_link' => "storage/$documentLink",
            'document_size' => $documentSize,
            'document_mime_type' => $documentMimeType
        ])->all());
    }
}
