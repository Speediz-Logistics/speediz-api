<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Delivery\MapResource;
use App\Models\Driver;
use App\Models\Package;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    use BaseApiResponse;

    //searchMap function to search map by package number
    public function searchMap($package_number)
    {
        $user = auth()->user();
        $driver = Driver::query()->where('user_id', $user->id)->first();

        // Ensure the driver exists
        if (!$driver) {
            return $this->failed('Driver not found', 404);
        }
        $package = Package::query()
            ->with([
                'vendor',
                'customer',
                'location',
                'driver',
                'shipment',
                'invoice',
            ])
            ->where('driver_id', $driver->id)
            ->where('number', $package_number)
            ->first();

        if (!$package) {
            return $this->failed('Package not found', 404);
        }
        // Return the package number
        return $this->success(MapResource::make($package), 'Package details');
    }
}
