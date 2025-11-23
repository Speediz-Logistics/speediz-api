<?php

namespace App\Http\Resources\Delivery;

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

            'driver_id' => $this->driver_id,
            'driver_name' => $this->driver ? $this->driver->first_name . $this->driver->last_name : null,

            'date' => $this->created_at->toDateString(),
            

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
