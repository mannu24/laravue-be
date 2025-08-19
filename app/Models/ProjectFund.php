<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFund extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'transaction_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
