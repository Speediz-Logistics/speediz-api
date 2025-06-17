<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    // Define the table name if it's not the plural form of the model name
    protected $table = 'shipments';

    // Define the fillable fields to allow mass assignment
    protected $fillable = [
        'package_id',
        'number',
        'type',
        'description',
        'date',
        'delivery_fee',
        'status',
    ];

    // Define the relationship with the Package model
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
