<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    //table currencies
    protected $table = 'currencies';

    protected $fillable = [
        'dollar',
        'exchange_rate',
    ];
}
