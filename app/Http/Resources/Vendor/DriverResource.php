<?php

namespace App\Http\Resources\Vendor;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'driver_type' => $this->driver_type,
            'driver_description' => $this->driver_description,
            'dob' =>  $this->dob ?? null,
            'gender' => $this->gender,
            'zone' => $this->zone,
            'contact_number' => $this->contact_number,
            'telegram_contact' => $this->telegram_contact,
            'image' => $this->image,
            'bank_name' => $this->bank_name,
            'bank_number' => $this->bank_number,
            'cv' => $this->cv,
            'address' => $this->address,
            'status' => (int) $this->user->account_status,
            'email' => $this->user->email,
        ];
    }
}
