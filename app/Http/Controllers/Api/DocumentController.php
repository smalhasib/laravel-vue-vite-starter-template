<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bot;
use App\Models\Source;
use App\Models\Document;
use App\Jobs\ProcessDocument;
use App\Jobs\DeleteRemoteDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Bot $bot, Source $source)
    {
        $this->authorize('update', $bot);

        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            // Update source status to queued
            $source->setQueued();

            // Dispatch job to process the document
            ProcessDocument::dispatch($source, $request->url);

            return response()->json([
                'message' => 'Document queued for processing',
                'status' => 'queued'
            ], 202);

        } catch (\Exception $e) {
            Log::error('Failed to queue document', [
                'source_id' => $source->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to queue document: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Bot $bot, Source $source, Document $document)
    {
        $this->authorize('update', $bot);

        try {
            // Get document chunks count before deletion for remote cleanup
            $chunksCount = $document->indexed_chunks_count;

            // Delete the document locally
            $document->delete();

            // Queue remote deletion if document had indexed chunks
            if ($chunksCount > 0) {
                DeleteRemoteDocument::dispatch(
                    $bot->user_id,
                    $bot->id,
                    $source->id,
                    $document->id,
                    $chunksCount
                );
            }

            // Update source status if needed
            if ($source->documents()->count() === 0) {
                $source->setQueued();
            } else {
                // Check if any documents are still being processed
                $pendingDocuments = $source->documents()
                    ->whereNull('indexed_chunks_count')
                    ->orWhere('indexed_chunks_count', 0)
                    ->count();

                if ($pendingDocuments > 0) {
                    $source->setIndexing();
                } else {
                    $source->setIndexed();
                }
            }

            return response()->json(['message' => 'Document deletion queued successfully']);

        } catch (\Exception $e) {
            Log::error('Failed to delete document', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to delete document'], 500);
        }
    }
}
