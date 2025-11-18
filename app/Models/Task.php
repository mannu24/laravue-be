<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskFrequency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'frequency',
        'xp_reward',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'frequency' => TaskFrequency::class,
            'xp_reward' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the users assigned to this task.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tasks')
            ->withPivot('status', 'completed_at', 'assigned_at')
            ->withTimestamps();
    }
}

