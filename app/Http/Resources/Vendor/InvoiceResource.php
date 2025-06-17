<?php

namespace App\Http\Resources\Vendor;

use Carbon\Carbon;
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
            'invoice_number' => $this->invoice_number,
            'date' => Carbon::parse($this->date)->format('d/m/Y H:i') ?? null,
            'total' => $this->total,
            'note' => $this->note,
            'status' => $this->status,
            'total_package_price' => $this->total_package_price,
            'total_delivery_fee' => $this->delivery_fee,
            'package_status' => $this->package_status_counts, // Added status counts
            'package' => new InvoicePackageResource($this->package),
            'driver' => new DriverResource($this->driver),
            'vendor' => new VendorResource($this->vendor),
        ];
    }
}
