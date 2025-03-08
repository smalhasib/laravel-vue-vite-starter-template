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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SourceController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Bot $bot)
    {
        $validated = $request->validate([
            'type' => 'required|in:URL,URL List,WordPress',
            'title' => 'nullable|string', // Title is now optional
            'url' => 'required_if:type,URL|string|url', // URL is required for URL type
            'refresh_schedule' => 'required|in:never,daily,weekly,monthly'
        ]);

        try {
            DB::beginTransaction();

            // Create source record
            $source = $bot->sources()->create([
                'user_id' => auth()->id(),
                'type' => $validated['type'],
                'title' => $validated['title'] ?? '', // Always provide a default empty string
                'status' => Source::STATUS_QUEUED,
                'refresh_schedule' => $validated['refresh_schedule'],
            ]);

            // Dispatch appropriate job based on source type
            if ($validated['type'] === 'URL') {
                ProcessUrlSource::dispatch($source, $validated['url']);
            } elseif ($validated['type'] === 'URL List') {
                ProcessUrlSource::dispatch($source);
            }

            DB::commit();

            Log::info('Source created and queued for processing', [
                'source_id' => $source->id,
                'bot_id' => $bot->id,
                'type' => $validated['type']
            ]);

            return response()->json([
                'message' => 'Source added successfully',
                'data' => $source->load('documents')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add source', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to add source: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Bot $bot, Source $source)
    {
        $this->authorize('delete', $source);

        try {
            DB::beginTransaction();

            // Get documents with chunks before deleting
            $documents = $source->documents()
                ->where('indexed_chunks_count', '>', 0)
                ->get(['id', 'indexed_chunks_count']);

            // Format documents data for remote deletion
            if ($documents->isNotEmpty()) {
                $documentsData = $documents->map(function ($document) {
                    return [
                        'documentId' => (int) $document->id,
                        'chunks' => (int) $document->indexed_chunks_count
                    ];
                })->toArray();

                // Queue remote deletion
                DeleteRemoteSource::dispatch(
                    (int) $source->user_id,
                    (int) $bot->id,
                    (int) $source->id,
                    $documentsData
                );
            }

            // Delete the local source record (will cascade to documents)
            $source->delete();

            DB::commit();

            return response()->json([
                'message' => 'Source deleted successfully',
                'queued_for_remote_deletion' => $documents->isNotEmpty()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete source', [
                'source_id' => $source->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get the current status of a source
     */
    public function status(Bot $bot, Source $source)
    {
        $this->authorize('view', $source);

        return response()->json($source->load([
            'documents' => function ($query) {
                $query->select('id', 'source_id', 'title', 'source', 'indexed_chunks_count');
            }
        ]));
    }
}
