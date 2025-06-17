<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageInvoiceResource extends JsonResource
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
            'customer_phone' => $this->customer?->phone,
            'location' => $this->location?->location,
            'package_date' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'package_price' => $this->price,
            'package_status' => $this->status,
        ];
    }
}
