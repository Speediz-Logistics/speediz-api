<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TrackingResource;
use App\Models\DeliveryTracking;
use App\Models\Package;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    use BaseApiResponse;
    //index
    public function index(Request $request)
    {
        $per_page = request()->query('per_page', config('pagination.per_page', 10));
        $search = request()->query('search'); // search by customer phone

        $tracking = Package::query()
            ->with(['location', 'driver', 'shipment'])
            ->when($search, fn($query, $search) => $query->whereHas('driver', fn($query) => $query->where('id', 'like', "%$search%")))
            ->where('status', 'in_transit')
            ->whereNotNull('driver_id')
            ->paginate($per_page);

        $data = [
            'data' => TrackingResource::collection($tracking),
            'pagination' => [
                'total' => $tracking->total(),
                'per_page' => $tracking->perPage(),
                'current_page' => $tracking->currentPage(),
                'last_page' => $tracking->lastPage(),
                'from' => $tracking->firstItem(),
                'to' => $tracking->lastItem()
            ]
        ];

        return $this->success($data, 'Tracking', 'Tracking data fetched successfully');
    }
    //show
    public function show($id)
    {
        $tracking = Package::query()
            ->with(['location', 'driver', 'shipment'])
            ->where('id', $id)
            ->first();

        if (!$tracking) {
            return $this->failed(null, 'Tracking', 'Tracking data not found', 404);
        }

        //get delivery_tracking
        $delivery_tracking = DeliveryTracking::query()
            ->where('package_id', $id)
            ->first();

        if (!$delivery_tracking) {
            return $this->failed(
                null,
                'Delivery Tracking Not Found',
                'The delivery tracking for the package does not exist.',
                404
            );
        }

        $tracking->delivery_tracking = $delivery_tracking;

        $data = new TrackingResource($tracking);
        return $this->success($data, 'Tracking', 'Tracking data fetched successfully');
    }
}
