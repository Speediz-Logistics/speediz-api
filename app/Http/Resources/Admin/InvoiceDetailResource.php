<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'shipment_code'    => $this->package?->shipment?->number,
            'receiver_contact' => $this->package?->customer?->phone,
            'location'         => $this->package?->location?->location,
            'package_date'     => Carbon::parse($this->created_at)->format('Y-m-d'),
            'package_price'    => $this->total,
            'cod'              => $this->package?->invoice?->status === "unpaid" ? 0 : $this->total,
            'delivery_fee'     => (int) $this->package?->shipment?->delivery_fee ?? 1.5, // Casting to int
            'package_status'   => $this->package?->status,
        ];
    }
}
