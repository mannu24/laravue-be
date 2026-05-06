<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioProject extends Model
{
    protected $fillable = [
        'portfolio_id', 'project_id', 'title', 'description',
        'image_path', 'tech_stack', 'live_url', 'source_url', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tech_stack' => 'json',
        ];
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function linkedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
