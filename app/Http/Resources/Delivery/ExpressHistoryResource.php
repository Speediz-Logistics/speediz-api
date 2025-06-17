<?php

namespace App\Http\Resources\Delivery;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpressHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_number' => $this->shipment->number,
            'status' => $this->status,
            'vendor_business_name' => $this->vendor->business_name,
            'customer_full_name' => $this->customer->first_name . ' ' . $this->customer->last_name,
            'customer_phone' => $this->customer->phone,
            'package_description' => $this->description,
            'package_date' => Carbon::parse($this->created_at)->format('d/m/Y H:i') ?? null,
            'package_price' => $this->price,
            'package_price_riel' => $this->price * 4100,
            'delivery_fee' => $this->shipment->delivery_fee,
        ];
    }
}