<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectVersion extends Model
{
    protected $fillable = [
        'project_id',
        'version_number',
        'changelog',
        'release_date',
        'download_url',
        'is_stable'
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_stable' => 'boolean',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeStable($query)
    {
        return $query->where('is_stable', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('release_date', 'desc');
    }

    // Methods
    public function markAsStable()
    {
        $this->update(['is_stable' => true]);
    }
}
