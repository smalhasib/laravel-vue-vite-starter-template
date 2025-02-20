<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_public',
        'model_type',
        'language',
        'status',
        'sources_count',
        'questions_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'sources_count' => 'integer',
        'questions_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
