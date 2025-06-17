<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? null,
            'image' => $this->image ?? null,
            'package' => [
                'package_number' => $this->number ?? null,
                'customer_name' => $this->customer->first_name . ' ' . $this->customer->last_name ?? null,
                'customer_phone' => $this->customer->phone ?? null,
                'location' => $this->location->location ?? null,
                'total_price' => $this->price ?? null,
                'status' => $this->status ?? null,
            ],
            'delivery' => [
                'shipment_date' => $this->shipment->date ?? null,
                'package_status' => $this->status ?? null,
                'driver_name' => $this->driver?->first_name . ' ' . $this->driver?->last_name ?? null,
                'driver_phone' => $this->driver->contact_number ?? null,
                'delivery_fee' => $this->shipment->delivery_fee ?? null,
            ],
            'vendor' => [
                'vendor_name' => $this->vendor->first_name . ' ' . $this->vendor->last_name ?? null,
                'pickup_date' => $this->shipment->date ?? null,
                'vendor_phone' => $this->vendor->contact_number ?? null,
                'vendor_address' => $this->vendor->address ?? null,
            ],
            'location' => [
                'location' => $this->location->location ?? null,
                'latitude' => $this->location->lat ?? null,
                'longitude' => $this->location->lng ?? null,
            ],
        ];
    }
}
