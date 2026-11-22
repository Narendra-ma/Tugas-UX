<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DocumentFileController extends Controller
{
    public function show(Document $document)
    {
        Gate::authorize('view', $document->booking);

        return Storage::download($document->path, $document->original_name);
    }
}
