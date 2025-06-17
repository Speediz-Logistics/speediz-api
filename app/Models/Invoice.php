<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    // Define the table name (optional if it's the plural form of the model name)
    protected $table = 'invoices';

    // Define the fillable attributes to protect against mass assignment
    protected $fillable = [
        'vendor_invoice_id',
        'customer_id',
        'vendor_id',
        'employee_id',
        'driver_id',
        'package_id',
        'number',
        'date',
        'total',
        'status',
        'note',
    ];

    // Optionally, you can define relationships if these foreign keys point to other models
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    //belong to package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    //belong to vendor invoice
    public function vendorInvoice()
    {
        return $this->belongsTo(VendorInvoice::class);
    }

    // Optionally, you can define the cast for 'date' and 'total' if needed
    protected $casts = [
        'date' => 'date',
        'total' => 'float',
    ];
}
