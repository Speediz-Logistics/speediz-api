<?php

namespace App\Http\Resources\Delivery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $driver = $this['driver'];

        return [
            'driver_name'       => $driver->first_name . ' ' . $driver->last_name,
            'count_all'         => $this['count_all'],
            'count_pending'     => $this['count_pending'],
            'count_in_transit'  => $this['count_in_transit'],
            'count_completed'   => $this['count_completed'],
            'count_cancelled'   => $this['count_cancelled'],
            'packages'          => $this['packages'],
        ];
    }
}
