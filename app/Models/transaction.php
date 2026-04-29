<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class transaction extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'amount', 'type'];
}
