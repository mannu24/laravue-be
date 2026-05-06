<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SkillProficiency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioSkill extends Model
{
    protected $fillable = ['portfolio_id', 'name', 'proficiency', 'sort_order'];

    protected function casts(): array
    {
        return [
            'proficiency' => SkillProficiency::class,
        ];
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
