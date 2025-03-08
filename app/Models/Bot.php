<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use App\Jobs\DeleteRemoteSource;
use App\Jobs\DeleteRemoteBot;

class Bot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'model_type',
        'language',
        'status',
        'total_indexed_chunks_count'
    ];

    protected $with = ['sources']; // Always eager load sources

    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'total_indexed_chunks_count' => 'integer'
    ];

    protected $visible = [
        'id',
        'name',
        'description',
        'is_public',
        'model_type',
        'language',
        'status',
        'sources_count',
        'questions_count',
        'total_indexed_chunks_count',
        'created_at',
        'updated_at',
        'sources'
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        // Before deleting the bot, queue source deletions
        static::deleting(function ($bot) {
            // Get all sources with documents that have chunks
            $sources = $bot->sources()->with([
                'documents' => function ($query) {
                    $query->where('indexed_chunks_count', '>', 0)
                        ->select('id', 'source_id', 'indexed_chunks_count');
                }
            ])->get();

            // Queue deletion jobs for each source
            $sourcesData = $sources->map(function ($source) {
                return [
                    'sourceId' => $source->id,
                    'documents' => $source->documents->toArray()
                ];
            })->toArray();

            DeleteRemoteBot::dispatch($bot->user_id, $bot->id, $sourcesData);

            Log::info('Queued deletion jobs for bot sources', [
                'bot_id' => $bot->id,
                'sources_count' => $sources->count()
            ]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }
}
