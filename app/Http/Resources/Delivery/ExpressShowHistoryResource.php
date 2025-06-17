<?php

namespace App\Http\Resources\Delivery;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpressShowHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_number' => $this->shipment->number,
            'status' => $this->status,
            'location' => $this->location->location,
            'vendor_business_name' => $this->vendor->business_name,

            'timeline' => [
                'package_create' => Carbon::parse($this->created_at)->format('d/m/Y H:i') ?? null,
                'package_picked_up' => Carbon::parse($this->picked_up_at)->format('d/m/Y H:i') ?? null,
                'delivered_at' => Carbon::parse($this->delivered_at)->format('d/m/Y H:i') ?? null,
            ],
            'vendor_full_name' => $this->vendor->first_name . ' ' . $this->vendor->last_name,
            'vendor_phone' => $this->vendor->contact_number,
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
