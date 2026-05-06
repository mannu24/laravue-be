<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioTestimonial extends Model
{
    protected $fillable = [
        'portfolio_id', 'author_name', 'author_role', 'author_company',
        'content', 'author_photo_url', 'sort_order',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
