<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTracking extends Model
{
    use HasFactory;

    protected $table = 'delivery_tracking';

    protected $fillable = [
        'package_id',
        'status',
        'lat',
        'lng',
    ];

    //one to one with package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
