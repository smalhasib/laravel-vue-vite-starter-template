<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Source extends Model
{
    use HasFactory;

    // Define status constants
    const STATUS_QUEUED = 'queued';
    const STATUS_INDEXING = 'indexing';
    const STATUS_INDEXED = 'indexed';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'bot_id',
        'user_id',
        'type',
        'title',
        'status',
        'refresh_schedule',
        'last_refresh_at',
        'next_refresh_at',
        'indexed_chunks_count',
        'data'
    ];

    protected $casts = [
        'last_refresh_at' => 'datetime',
        'next_refresh_at' => 'datetime',
        'indexed_chunks_count' => 'integer',
        'data' => 'array'
    ];

    /**
     * Get all available source statuses
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_QUEUED,
            self::STATUS_INDEXING,
            self::STATUS_INDEXED,
            self::STATUS_FAILED
        ];
    }

    /**
     * Set source as queued
     */
    public function setQueued(): void
    {
        $this->status = self::STATUS_QUEUED;
        $this->save();
    }

    /**
     * Set source as indexing
     */
    public function setIndexing(): void
    {
        $this->status = self::STATUS_INDEXING;
        $this->save();
    }

    /**
     * Set source as indexed
     */
    public function setIndexed(): void
    {
        $this->status = self::STATUS_INDEXED;
        $this->save();
    }

    /**
     * Set source as failed
     */
    public function setFailed(): void
    {
        $this->status = self::STATUS_FAILED;
        $this->save();
    }

    /**
     * Check if source is queued
     */
    public function isQueued(): bool
    {
        return $this->status === self::STATUS_QUEUED;
    }

    /**
     * Check if source is indexing
     */
    public function isIndexing(): bool
    {
        return $this->status === self::STATUS_INDEXING;
    }

    /**
     * Check if source is indexed
     */
    public function isIndexed(): bool
    {
        return $this->status === self::STATUS_INDEXED;
    }

    /**
     * Check if source is failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Add documents relationship
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
