<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class VendorResource extends JsonResource
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
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'business_description' => $this->business_description,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'contact_number' => $this->contact_number,
            'image' => $this->image,
            'bank_name' => $this->bank_name,
            'bank_number' => $this->bank_number,
            'status' => $this->user->account_status,
            'email' => $this->user->email,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}

