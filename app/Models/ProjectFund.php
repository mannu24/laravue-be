<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFund extends Model
{
    protected $table = 'project_funds';

    protected $fillable = [
        'project_id',
        'user_id',
        'transaction_id'
    ];
}
