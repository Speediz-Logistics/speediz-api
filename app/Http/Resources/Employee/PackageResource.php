<?php

namespace App\Http\Resources\Employee;

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
            'sender' => [
                'name' => optional($this->vendor)->first_name && optional($this->vendor)->last_name
                    ? optional($this->vendor)->first_name . ' ' . optional($this->vendor)->last_name
                    : null,
                'phone' => optional($this->vendor)->contact_number,
            ],
            'receiver' => [
                'name' => optional($this->customer)->first_name && optional($this->customer)->last_name
                    ? optional($this->customer)->first_name . ' ' . optional($this->customer)->last_name
                    : null,
                'phone' => optional($this->customer)->phone,
            ],
            'branch' => [
                'name' => optional($this->branch)->name,
                'address' => optional($this->branch)->address,
                'phone' => optional($this->branch)->phone,
            ],
            'package_type' => [
                'name' => optional($this->package_type)->name,
                'description' => optional($this->package_type)->description,
            ],
            'package' => [
                'price' => number_format($this->price) ?? null,
                'price_khr' => number_format($this->price_khr)?? null,
                'note' => $this->note ?? null,
                'status' => $this->status ?? null,
            ],
            'delivery_fee' => [
                'price' => optional($this->delivery_fee) ? number_format($this->delivery_fee) : null,
            ],
            'driver' => [
                'name' => $this->driver ? $this->driver->first_name . ' ' . $this->driver->last_name : null,
                'phone' => optional($this->driver)->contact_number,
                'telegram_contact' => optional($this->driver)->telegram_contact,
            ],
        ];
    }
}
