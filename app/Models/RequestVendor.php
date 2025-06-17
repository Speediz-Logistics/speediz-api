<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestVendor extends Model
{
    use HasFactory;

    // Define the table name (optional if it follows Laravel's convention)
    protected $table = 'request_vendors';

    // Define the primary key (optional if it follows Laravel's convention)
    protected $primaryKey = 'id';

    // Define which attributes can be mass assigned
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'business_name',
        'business_type',
        'business_description',
        'dob',
        'gender',
        'address',
        'lat',
        'lng',
        'contact_number',
        'image',
        'bank_name',
        'bank_number',
        'status',
    ];

    // Define any attributes that should be cast to a specific data type
    protected $casts = [
        'dob' => 'date',
    ];

    // Optionally, if you want to disable timestamps (if you don't need them)
    public $timestamps = true;
}
