<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Storage\Document;

class DocumentSafeObserver
{
    public function creating(Document $document)
    {
        $instance = new ($document->document_type);

        $exists = $instance->where('uuid', $document->document_uuid)->exists();

        $values = $exists ? ['updated_id' => auth()->user()->id] : ['created_id' => auth()->user()->id];

        ActivityLog::updateOrCreate([
            'activity_type' => $document->document_type,
            'activity_id' => $document->document_uuid
        ], $values);
    }
}
