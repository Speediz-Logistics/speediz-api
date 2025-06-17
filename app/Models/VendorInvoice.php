<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorInvoice extends Model
{
    use HasFactory;

    protected $table = 'vendor_invoice';

    protected $fillable = [
        'vendor_id',
        'invoice_number',
        'description',
        'total',
        'status',
    ];

    //cast created_at and updated_at to datetime Y-m-d
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    //invoice
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
