<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'body',
        'model',
        'confidence_score',
        'is_helpful',
        'is_incorrect',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_helpful' => 'boolean',
        'is_incorrect' => 'boolean',
        'confidence_score' => 'float',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
