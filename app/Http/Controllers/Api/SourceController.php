<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessUrlSource;
use App\Jobs\DeleteRemoteSource;
use App\Models\Bot;
use App\Models\Source;
use App\Services\IndexingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SourceController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Bot $bot)
    {
        $this->authorize('update', $bot);

        Log::info('Adding new source to bot', [
            'bot_id' => $bot->id,
            'type' => $request->type
        ]);

        try {
            // Validate common fields
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:URL,URL List',
                'refresh_schedule' => 'required|string|in:never,daily,weekly,monthly',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 422);
            }

            // Handle different source types
            switch ($request->type) {
                case 'URL':
                    return $this->handleUrlSource($request, $bot);
                case 'URL List':
                    return $this->handleUrlListSource($request, $bot);
                default:
                    return response()->json(['message' => 'Unsupported source type'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to add source', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to add source: ' . $e->getMessage()], 500);
        }
    }

    private function handleUrlSource(Request $request, Bot $bot)
    {
        // Additional validation for URL source
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:2048',
            'title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Create source
        $source = $bot->sources()->create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'title' => $request->title,
            'refresh_schedule' => $request->refresh_schedule,
            'status' => Source::STATUS_QUEUED
        ]);

        // Dispatch job to process the URL
        ProcessUrlSource::dispatch($source, $request->url);

        return response()->json([
            'message' => 'Source added successfully',
            'data' => $source
        ], 201);
    }

    private function handleUrlListSource(Request $request, Bot $bot)
    {
        // Validate file type and title
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:txt,csv',
            'title' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please upload a CSV or TXT file'], 422);
        }

        $file = $request->file('file');

        // Store file
        $path = $file->store('url-lists', 'local');

        // Create source with file name as title
        $source = $bot->sources()->create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'title' => $request->title,
            'data' => [
                'file_path' => $path
            ],
            'refresh_schedule' => $request->refresh_schedule,
            'status' => Source::STATUS_QUEUED
        ]);

        // Dispatch job to process URLs
        ProcessUrlSource::dispatch($source);

        return response()->json([
            'message' => 'URL list file uploaded successfully. Processing will begin shortly.',
            'data' => [
                'source' => $source
            ]
        ], 201);
    }

    public function show(Bot $bot, Source $source)
    {
        $this->authorize('view', $source);
        return response()->json($source->load([
            'documents' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]));
    }

    public function status(Bot $bot, Source $source)
    {
        $this->authorize('view', $source);
        return response()->json($source->load([
            'documents' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]));
    }

    public function destroy(Bot $bot, Source $source)
    {
        $this->authorize('delete', $source);

        try {
            // Get documents with chunks before deletion
            $documents = $source->documents()
                ->where('indexed_chunks_count', '>', 0)
                ->select('id', 'indexed_chunks_count')
                ->get()
                ->map(function ($doc) {
                    return [
                        'documentId' => (int) $doc->id,
                        'chunks' => (int) $doc->indexed_chunks_count
                    ];
                })
                ->toArray();

            // Delete the source locally
            $source->delete();

            // Queue remote deletion if there are documents with chunks
            if (!empty($documents)) {
                DeleteRemoteSource::dispatch(
                    (int) $bot->user_id,
                    (int) $bot->id,
                    (int) $source->id,
                    $documents
                );
            }

            return response()->json(['message' => 'Source deletion queued successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete source', [
                'source_id' => $source->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to delete source'], 500);
        }
    }
}
