<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'package_id',
        'driver_id',
        'shipment_id',
        'delivery_tracking_id',
    ];

    //one to one with package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    //one to one with driver
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    //one to one with shipment
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    //one to one with delivery tracking
    public function deliveryTracking()
    {
        return $this->belongsTo(DeliveryTracking::class);
    }
}
