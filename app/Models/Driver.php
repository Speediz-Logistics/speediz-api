<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    // Define the table name (optional if it's the plural form of the model name)
    protected $table = 'drivers';

    // Define the fillable attributes to protect against mass assignment
    protected $fillable = [
        'first_name',
        'last_name',
        'driver_type',
        'driver_description',
        'dob',
        'gender',
        'zone',
        'contact_number',
        'telegram_contact',
        'image',
        'bank_name',
        'bank_number',
        'cv',
        'address',
        'user_id',
    ];

    // Optionally, you can define relationships if the driver is related to other models (e.g., User)
    public function user()
    {
        return $this->belongsTo(User::class);  // Assuming the 'user_id' is a foreign key to the User model
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    // Optionally, you can define the cast for 'dob' if you want it as a date type
    protected $casts = [
        'dob' => 'date',
    ];

    //cv
    public function getCvAttribute($value)
    {
        return $value ? asset('' . $value) : null;
    }
}
