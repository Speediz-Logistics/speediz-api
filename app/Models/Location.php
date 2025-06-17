<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    // Define the table name (if it's different from the default plural of the model name)
    protected $table = 'locations';

    // Define the fillable attributes to protect against mass assignment
    protected $fillable = [
        'location',
        'lat',
        'lng',
    ];

    // Optionally, you can define the cast for lat and lng if you want them as floats
    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];
}
