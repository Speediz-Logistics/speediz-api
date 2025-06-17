<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->driver ? $this->driver->id : null, // Check if driver exists
            'package_id' => $this->id ?? 'N/A', // Use null coalescing for package ID
            'driver_name' => $this->driver ? $this->driver->first_name . ' ' . $this->driver->last_name : 'N/A', // Handle missing driver
            'location' => [
                'address' => $this->location->location ?? 'N/A', // Use null coalescing for location
                'lat' => $this->location->lat ?? 'N/A', // Use null coalescing for latitude
                'lng' => $this->location->lng ?? 'N/A', // Use null coalescing for longitude
            ],
            'delivery_tracking' => $this->delivery_tracking ?? 'N/A', // Handle missing delivery tracking
            'driver_phone' => $this->driver ? $this->driver->contact_number : 'N/A', // Handle missing phone
            'status' => $this->status ?? 'Unknown', // Handle missing status
        ];
    }
}
