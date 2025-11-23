<?php

namespace App\Http\Resources\Delivery;

use App\Http\Resources\Vendor\DriverResource;
use App\Http\Resources\Vendor\PackageResource;
use App\Http\Resources\Vendor\VendorResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'vendor_invoice_id' => $this->vendor_invoice_id,
            'customer_id' => $this->customer_id,
            'vendor_id' => $this->vendor_id,
            'employee_id' => $this->employee_id,
            'driver_id' => $this->driver_id,

            'package_id' => $this->package_id,
            'number' => $this->number,
            'date' => $this->date,
            'total' => $this->total,
            'status' => $this->status,
            'note' => $this->note,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // ----- Computed Fields -----
            'total_package_price' => $this->total_package_price ?? 0,
            'delivery_fee' => $this->delivery_fee ?? 0,

            'package_status_counts' => [
                'cancelled' => $this->package_status_counts['cancelled'] ?? 0,
                'pending' => $this->package_status_counts['pending'] ?? 0,
                'in_transit' => $this->package_status_counts['in_transit'] ?? 0,
                'completed' => $this->package_status_counts['completed'] ?? 0,
            ],

            // ----- Relations -----
            'packages' => PackageResource::collection(
                $this->whenLoaded('packages')
            ),

            'driver' => new DriverResource(
                $this->whenLoaded('driver')
            ),

            'vendor' => new VendorResource(
                $this->whenLoaded('vendor')
            ),
        ];
    }
}
