<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactQuery extends Model
{
    protected $table = 'contact_queries';

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'subject',
        'message',
        'status'
    ];
}
