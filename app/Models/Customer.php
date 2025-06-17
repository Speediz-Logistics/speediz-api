<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // Define the table name (optional if it's the plural form of the model name)
    protected $table = 'customers';

    // Define the fillable attributes to protect against mass assignment
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
    ];

    // Optionally, you can define any relationships if needed (e.g., if Customer has invoices)
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
