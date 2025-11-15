<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GitHubImport extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'github_owner',
        'github_repo',
        'github_repo_id',
        'github_full_name',
        'imported_data',
        'imported_at',
    ];

    protected $casts = [
        'imported_data' => 'array',
        'imported_at' => 'datetime',
    ];

    /**
     * Get the user that owns the import.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project that was imported.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
