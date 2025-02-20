<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BotController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        Log::info('Fetching bots for user', ['user_id' => auth()->id()]);
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
        Log::info('Viewing bot details', ['bot_id' => $bot->id, 'user_id' => auth()->id()]);
        $this->authorize('view', $bot);  // This is the proper way to call authorize
        return $bot;
    }

    public function destroy(Bot $bot)
    {
        Log::info('Attempting to delete bot', ['bot_id' => $bot->id, 'user_id' => auth()->id()]);
        $this->authorize('delete', $bot);
        
        try {
            $bot->delete();
            Log::info('Bot deleted successfully', ['bot_id' => $bot->id]);
            return response()->noContent();
        } catch (\Exception $e) {
            Log::error('Failed to delete bot', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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
}
