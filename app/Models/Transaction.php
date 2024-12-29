<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'gateway_id',
        'amount',
        'mode',
        'status',
        'payment_status'
    ];
}
