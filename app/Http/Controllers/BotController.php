<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function index()
    {
        return auth()->user()->bots;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'required|boolean',
            'model_type' => 'required|string',
            'language' => 'required|string',
        ]);

        $bot = auth()->user()->bots()->create([
            ...$validated,
            'status' => 'Awaiting Sources',
            'sources_count' => 0,
            'questions_count' => 0,
        ]);

        return response()->json($bot, 201);
    }

    public function destroy(Bot $bot)
    {
        $this->authorize('delete', $bot);
        $bot->delete();
        return response()->noContent();
    }

    public function update(Request $request, Bot $bot)
    {
        $this->authorize('update', $bot);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'required|boolean',
            'model_type' => 'required|string',
            'language' => 'required|string',
        ]);

        $bot->update($validated);
        return $bot;
    }
}
