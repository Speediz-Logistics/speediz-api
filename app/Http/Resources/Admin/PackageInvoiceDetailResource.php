<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageInvoiceDetailResource extends JsonResource
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
            'personal' => [
                'sender_name' => ($this->vendor?->first_name ?? '') . ' ' . ($this->vendor?->last_name ?? ''),
                'sender_phone_number' => $this->vendor?->contact_number ?? null,
                'receiver_phone' => $this->customer?->phone ?? null,
                'branch' => $this->branch?->name ?? null,
                'destination' => $this->location?->location ?? null,
            ],
            'package_info' => [
                'package_type' => $this->package_type?->name ?? null,
                'package_name' => $this->name ?? null,
                'package_price' => $this->price ?? 0,
                'package_price_riel' => ($this->price ?? 0) * 4100,
            ],
            'delivery_fee' => [
                'delivery_fee' => $this->shipment?->delivery_fee ?? 0,
                'delivery_contact' => $this->driver?->contact_number ?? null,
                'delivery_telegram' => $this->driver?->telegram_contact ?? null,
                'status' => $this->status ?? null,
                'note' => $this->invoice?->note ?? null,
            ],
        ];
    }
}
