<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    protected $fillable = ['booking_id', 'name', 'phone', 'plate', 'reason', 'arrears_amount'];
}
