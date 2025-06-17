<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
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
            'driver_type' => $this->driver_type,
            'driver_description' => $this->driver_description,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'zone' => $this->zone,
            'contact_number' => $this->contact_number,
            'telegram_contact' => $this->telegram_contact,
            'image' => $this->image,
            'bank_name' => $this->bank_name,
            'bank_number' => $this->bank_number,
            'status' => $this->user->account_status,
            'email' => $this->user->email,
            'cv' => $this->cv,
            'address' => $this->address,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
