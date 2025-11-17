<?php

namespace App\Http\Resources\Vendor;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
            'package_number' => $this->number,
            'customer_name' => $this->customer->first_name . ' ' . $this->customer->last_name,
            'customer_phone' => $this->customer->phone,
            'location' => $this->location->location,
            'date' => Carbon::parse($this->created_at)->format('d/m/Y H:i') ?? null,
            'image' => $this->image,
            'zone' => $this->zone,
            'status' => $this->status,
            'driver' => new DriverResource($this->driver),
            'customer' => new CustomerResource($this->customer),
            'destination' => new LocationResource($this->location),
            'shipment' => new ShipmentResource($this->shipment),
            'driver_location' => $this->whenLoaded('deliveryTracking'),
        ];
    }
}
