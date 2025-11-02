<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rollback extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'package_id',
        'reason',
        'user_id',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Package model
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
