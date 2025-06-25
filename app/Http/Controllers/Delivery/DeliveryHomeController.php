<?php

namespace App\Http\Controllers\Delivery;

use App\Constants\ConstPackageStatus;
use App\Constants\ConstShipmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Delivery\HomeResource;
use App\Models\DeliveryTracking;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Revenue;
use App\Models\Shipment;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DeliveryHomeController extends Controller
{
    use BaseApiResponse;
    public function index(Request $request)
    {
        $user = auth()->user();
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        $search = $request->query('search');
        $driver = Driver::query()->where('user_id', $user->id)->first();

        if (!$driver) {
            return $this->error('Driver not found', 404);
        }

        //packages belong to driver
        $packages = Package::query()
            ->when($search, function ($query, $search) {
                return $query->where('number', 'like', '%' . $search . '%');
            })
            ->where('driver_id', $driver->id)
            //order status in_transit first
            ->paginate($perPage);

        $count_all = Package::query()->where('driver_id', $driver->id)->count();
        $count_pending = Package::query()->where('driver_id', $driver->id)->where('status', ConstPackageStatus::PENDING)->count();
        $count_in_transit = Package::query()->where('driver_id', $driver->id)->where('status', ConstPackageStatus::IN_TRANSIT)->count();
        $count_completed = Package::query()->where('driver_id', $driver->id)->where('status', ConstPackageStatus::COMPLETED)->count();
        $count_cancelled = Package::query()->where('driver_id', $driver->id)->where('status', ConstPackageStatus::CANCELLED)->count();

        $data = new HomeResource([
            'driver' => $driver,
            'user' => $user,
            'count_all' => $count_all,
            'count_pending' => $count_pending,
            'count_in_transit' => $count_in_transit,
            'count_completed' => $count_completed,
            'count_cancelled' => $count_cancelled,
            'packages' => $packages
        ]);

        return $this->success($data,'Welcome to delivery home page');
    }

    //pickupPackage
    public function pickupPackage(Request $request)
    {
        $user = auth()->user();
        $driver = Driver::query()->where('user_id', $user->id)->first();
        $package_id = $request->id;

        if (!$driver) {
            return $this->error('Driver not found', 404);
        }
        //update package status to picked up
        //Assign Driver Id
        Package::query()->where('id', $package_id)->update(['status' => ConstPackageStatus::IN_TRANSIT]);
        //picked_up_at
        Package::query()->where('id', $package_id)->update(['picked_up_at' => now()]);
        //update shipment status to in transit
        Shipment::query()->where('package_id', $package_id)->update(['status' => ConstShipmentStatus::IN_TRANSIT]);
        //add driver id to invoice
        Invoice::query()->where('package_id', $package_id)->update(['driver_id' => $driver->id]);
        //create delivery tracking
        $tracking = new DeliveryTracking();
        $tracking->package_id = $package_id;
        $tracking->status = ConstPackageStatus::IN_TRANSIT;
        $tracking->save();

        return $this->success(null,'Package picked up successfully');
    }

    //deliveredPackage
    public function deliveredPackage(Request $request)
    {
        $user = auth()->user();
        $driver = Driver::query()->where('user_id', $user->id)->first();
        $package_id = $request->id;

        if (!$driver) {
            return $this->error('Driver not found', 404);
        }
        //update package status to delivered
        Package::query()->where('id', $package_id)->update(['status' => ConstPackageStatus::COMPLETED]);
        //delivered_at
        Package::query()->where('id', $package_id)->update(['delivered_at' => now()]);
        //update shipment status to delivered
        Shipment::query()->where('package_id', $package_id)->update(['status' => ConstShipmentStatus::COMPLETED]);
        //update delivery tracking status to delivered
        DeliveryTracking::query()->where('package_id', $package_id)->update(['status' => ConstPackageStatus::COMPLETED]);
        //query delivery_fee from shipment by package_id
        $delivery_fee = Shipment::query()->where('package_id', $package_id)->first()->delivery_fee;
        //add delivery_fee to revenue
        $revenue = Revenue::create([
            'name' => 'Delivery Fee' . Carbon::now()->format('Y-m-d'),
            'description' => $driver->name . 'Delivery Fee' . Carbon::now()->format('Y-m-d') . 'Package ID' . $package_id,
            'amount' => $delivery_fee ?? 1.5,
        ]);

        return $this->success([
            'revenue' => $revenue,
            'driver' => $driver,
            'package_id' => $package_id,
        ],'Package delivered successfully');
    }

    //realtimeTracking
    public function realtimeTracking(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;

        // Find existing tracking record or create a new one
        $tracking = DeliveryTracking::updateOrCreate(
            ['package_id' => $request->package_id], // Condition to check
            [
                'lat' => $lat,
                'lng' => $lng,
                'status' => ConstPackageStatus::IN_TRANSIT,
            ]
        );

        return $this->success($tracking, 'Realtime tracking updated successfully');
    }
}
