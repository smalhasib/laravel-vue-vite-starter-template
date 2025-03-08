<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bot;
use App\Services\IndexingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use App\Jobs\DeleteRemoteBot;

class BotController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        return auth()->user()->bots;
    }

    public function store(Request $request)
    {
        Log::info('Creating new bot', ['user_id' => auth()->id(), 'data' => $request->all()]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'required|boolean',
            'model_type' => 'required|string',
            'language' => 'required|string',
        ]);

        try {
            $bot = auth()->user()->bots()->create([
                ...$validated,
                'status' => 'Awaiting Sources',
                'sources_count' => 0,
                'questions_count' => 0,
            ]);

            Log::info('Bot created successfully', ['bot_id' => $bot->id]);
            return response()->json($bot, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create bot', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function show(Bot $bot)
    {
        $this->authorize('view', $bot);
        return response()->json($bot->load([
            'sources' => function ($query) {
                $query->select('id', 'bot_id', 'type', 'status', 'refresh_schedule', 'indexed_chunks_count', 'title')
                    ->with([
                        'documents' => function ($q) {
                            $q->select('id', 'source_id', 'title', 'source', 'indexed_chunks_count');
                        }
                    ]);
            }
        ]));
    }

    public function update(Request $request, Bot $bot)
    {
        Log::info('Attempting to update bot', ['bot_id' => $bot->id, 'user_id' => auth()->id(), 'data' => $request->all()]);
        $this->authorize('update', $bot);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'required|boolean',
            'model_type' => 'required|string',
            'language' => 'required|string',
        ]);

        try {
            $bot->update($validated);
            Log::info('Bot updated successfully', ['bot_id' => $bot->id]);
            return $bot;
        } catch (\Exception $e) {
            Log::error('Failed to update bot', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function destroy(Bot $bot)
    {
        $this->authorize('delete', $bot);

        try {
            DB::beginTransaction();

            // Get all sources with documents that have chunks
            $sources = $bot->sources()->with([
                'documents' => function ($query) {
                    $query->where('indexed_chunks_count', '>', 0)
                        ->select('id', 'source_id', 'indexed_chunks_count');
                }
            ])->get();

            // Format sources data for remote deletion
            $sourcesWithChunks = $sources->filter(function ($source) {
                return $source->documents->isNotEmpty();
            })->map(function ($source) {
                return [
                    'sourceId' => (int) $source->id,
                    'documents' => $source->documents->map(function ($document) {
                        return [
                            'documentId' => (int) $document->id,
                            'chunks' => (int) $document->indexed_chunks_count
                        ];
                    })->toArray()
                ];
            })->values()->toArray();

            // Queue remote deletion if there are sources with chunks
            if (!empty($sourcesWithChunks)) {
                DeleteRemoteBot::dispatch(
                    (int) $bot->user_id,
                    (int) $bot->id,
                    $sourcesWithChunks
                );
            }

            // Delete the local bot record (will cascade to sources and documents)
            $bot->delete();

            DB::commit();

            return response()->json([
                'message' => 'Bot deleted successfully',
                'queued_for_remote_deletion' => !empty($sourcesWithChunks)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete bot', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
