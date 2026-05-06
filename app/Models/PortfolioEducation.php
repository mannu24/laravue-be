<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioEducation extends Model
{
    protected $table = 'portfolio_educations';

    protected $fillable = [
        'portfolio_id', 'institution', 'degree', 'field',
        'start_year', 'end_year', 'sort_order',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
