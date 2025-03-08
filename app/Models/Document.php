<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'title',
        'content',
        'source',
        'indexed_chunks_count'
    ];

    protected $casts = [
        'indexed_chunks_count' => 'integer'
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
