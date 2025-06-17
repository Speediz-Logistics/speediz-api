<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        "first_name",
        "last_name",
        "business_name",
        "business_type",
        "business_description",
        "dob",
        "gender",
        "address",
        'lat',
        'lng',
        "contact_number",
        "image",
        "bank_name",
        "bank_number",
        "user_id"
    ];

    public function products()
    {
        return $this->hasMany(Products::class);
    }

    //relation to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //invoices
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    //vendor invoice
    public function vendorInvoice()
    {
        return $this->hasMany(VendorInvoice::class);
    }
}