<?php

namespace App\Http\Resources\Vendor;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $vendor = $this['vendor'];
        $user = $this['user'];

        return [
            'full_name'       => $vendor?->first_name . ' ' . $vendor?->last_name,
            'email'           => $user?->email,
            'contact_number'  => $vendor?->contact_number,
            'first_name'      => $vendor?->first_name,
            'last_name'       => $vendor?->last_name,
            'business_name'   => $vendor?->business_name,
            'image'           => $vendor?->image,
            'dob'             => $vendor?->dob,
            'gender'          => $vendor?->gender,
            'address'         => $vendor?->address,
        ];
    }
}
