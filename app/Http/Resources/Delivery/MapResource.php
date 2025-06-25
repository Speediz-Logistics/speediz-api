<?php

namespace App\Http\Resources\Delivery;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MapResource extends JsonResource
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
            'vendor' => [
                'name' => $this->vendor?->first_name . ' ' . $this->vendor?->last_name,
                'phone' => $this->vendor?->contact_number,
                'address' => $this->vendor?->address,
                'lat' => $this->vendor?->lat,
                'lng' => $this->vendor?->lng,
            ],
            'customer' => [
                'name' => $this->customer?->first_name . ' ' . $this->customer?->last_name,
                'phone' => $this->customer?->phone,
                'address' => $this->location?->location,
                'lat' => $this->location?->lat,
                'lng' => $this->location?->lng,
            ],
            'package' => [
                'price' => $this->price,
                'delivery_fee' => $this->shipment?->delivery_fee,
                'status' => $this->shipment?->status,
            ]
        ];
    }
}
