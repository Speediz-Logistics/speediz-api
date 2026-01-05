<?php

namespace App\Http\Controllers\Delivery;

use App\Constants\ConstPackageStatus;
use App\Constants\ConstRollbackType;
use App\Constants\ConstShipmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Delivery\HomeResource;
use App\Models\DeliveryTracking;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Revenue;
use App\Models\Rollback;
use App\Models\Shipment;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
            return $this->failed(null,'Driver', 'Driver not found', 404);
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
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return $this->failed(null, 'Driver', 'Driver not found', 404);
        }

        $package_id = $request->id;
        $package = Package::query()->where('id', $package_id)
            ->first();

        if (!$package) {
            return $this->failed(null, 'Package', 'Package not found', 404);
        }

        DB::beginTransaction();
        try {

            // Update Shipment
            Shipment::where('package_id', $request->id)
                ->update(['status' => ConstShipmentStatus::IN_TRANSIT]);

            $shipment = Shipment::where('package_id', $request->id)->first();

            //if shipment not found
            if (!$shipment) {
                return $this->failed(null, 'Shipment not found for this package', 404);
            }
            // Update Invoice (assign driver)
            Invoice::where('package_id', $request->id)
                ->update(['driver_id' => $driver->id]);

            //get invoice id
            $invoice = Invoice::where('package_id', $request->id)->first();

            if (!$invoice) {
                return $this->failed(null, 'Invoice not found for this package', 404);
            }

            // Create Delivery Tracking
            $deliveryTracking = DeliveryTracking::create([
                'package_id' => $package_id,
                'status' => ConstPackageStatus::IN_TRANSIT,
                'lat' => $request->lat ?? null,
                'lng' => $request->lng ?? null,
            ]);

            logger('Delivery Tracking Created: ', ['delivery_tracking_id' => $deliveryTracking->id]);
            logger('Package Pickup by Driver: ', ['driver_id' => $driver->id, 'package_id' => $package->id]);
            logger('Shipment Updated: ', ['shipment_id' => $shipment->id ?? null, 'status' => ConstShipmentStatus::IN_TRANSIT]);
            logger('Invoice Updated: ', ['invoice_id' => $invoice->id ?? null, 'driver_id' => $driver->id]);

            // Update Package
            $package->update([
                'status' => ConstPackageStatus::IN_TRANSIT,
                'delivery_tracking_id' => $deliveryTracking->id,
                'shipment_id' => $shipment->id ?? null,
                'invoice_id' => $invoice->id ?? null,
                'picked_up_at' => now()
            ]);

            DB::commit();

            return $this->success($package, 'Package picked up successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(null,'Failed to update: ' . $e->getMessage(), 500);
        }
    }

    //deliveredPackage
    public function deliveredPackage(Request $request)
    {
        $user = auth()->user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return $this->failed(null, 'Driver', 'Driver not found', 404);
        }

        $package_id = $request->id;

        try {
            DB::beginTransaction();

            // Verify package exists
            $package = Package::query()->where('id', $package_id)->first();
            if (!$package) {
                return $this->failed(null,'Package not found', 'Package not found', 404);
            }

            // Update package
            $package->update([
                'status' => ConstPackageStatus::COMPLETED,
                'delivered_at' => now()
            ]);

            // Update shipment
            $shipment = Shipment::where('package_id', $package_id)
                ->update(['status' => ConstShipmentStatus::COMPLETED]);

            // Update delivery tracking
            $deliveryTracking = DeliveryTracking::where('package_id', $package_id)->first();

            if ($deliveryTracking) {
                $deliveryTracking->update([
                    'status' => ConstPackageStatus::COMPLETED
                ]);
            } else {
                return $this->failed(null, 'Delivery tracking not found', 404);
            }


            // Get delivery fee
            $shipment = Shipment::where('package_id', $package_id)->first();
            $delivery_fee = $shipment->delivery_fee ?? 1.5;

            // Create revenue
            $revenue = Revenue::create([
                'driver_id' => $driver->id ?? null,
                'package_id' => $package->id ?? null,
                'shipment_id' => $shipment->id ?? null,
                'delivery_tracking_id' => $deliveryTracking->id ?? null,
                'name' => 'Delivery Fee ' . now()->format('Y-m-d'),
                'description' => "{$driver->name} Delivery Fee for Package ID {$package_id}",
                'amount' => $delivery_fee,
            ]);

            DB::commit();

            return $this->success([
                'revenue' => $revenue,
                'driver' => $driver,
                'package' => $package,
            ], 'Package delivered successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(null,'Failed to update package: ' . $e->getMessage(), 500);
        }
    }

    //rollbackDeliveredPackage
    public function rollbackCompletedPackage(Request $request)
    {
        $user = auth()->user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return $this->failed(null, 'Driver', 'Driver not found', 404);
        }

        $package_id = $request->id;

        try {
            DB::beginTransaction();

            // Verify package exists
            $package = Package::query()
                ->where('id', $package_id)
                ->first();
            if (!$package) {
                return $this->failed(null,'Package not found', 'Package not found', 404);
            }

            // Update package
            $package->update([
                'status' => ConstPackageStatus::PENDING,
                'delivered_at' => null
            ]);

            // Update shipment
            Shipment::where('package_id', $package_id)
                ->update(['status' => ConstShipmentStatus::PENDING]);

            // Update delivery tracking
            DeliveryTracking::where('package_id', $package_id)
                ->update(['status' => ConstPackageStatus::PENDING]);

            //create rollback log
            Rollback::query()->create([
                'type' => ConstRollbackType::DELIVERY_ROLLBACK,
                'package_id' => $package->id,
                'reason' => $request->reason ?? 'No reason provided',
                'user_id' => $user->id,
            ]);

            DB::commit();

            return $this->success($package, 'Package rolled back to in pending successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(null,'Failed to update package: ' . $e->getMessage(), 500);
        }
    }

    //cancelPackage cancelDelivery during delivery
    public function cancelDelivery(Request $request)
    {
        $user = auth()->user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return $this->failed(null, 'Driver', 'Driver not found', 404);
        }

        $package_id = $request->id;

        try {
            DB::beginTransaction();

            // Verify package exists
            $package = Package::query()
                ->where('number', $package_id)
                ->first();

            if (!$package) {
                return $this->failed(null,'Package not found', 'Package not found', 404);
            }

            // Update package
            $package->update([
                'status' => ConstPackageStatus::CANCELLED,
            ]);

            // Update shipment
            Shipment::where('package_id', $package_id)
                ->update(['status' => ConstShipmentStatus::CANCELLED]);

            // Update delivery tracking
            DeliveryTracking::where('package_id', $package_id)
                ->update(['status' => ConstPackageStatus::CANCELLED]);

            //rollback log
            Rollback::query()->create([
                'type' => ConstRollbackType::DELIVERY_CANCEL,
                'package_id' => $package->id,
                'reason' => $request->reason ?? 'No reason provided',
                'user_id' => $user->id,
            ]);

            DB::commit();

            return $this->success($package, 'Package cancelled successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(null,'Failed to cancel package: ' . $e->getMessage(), 500);
        }
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

    //notifications
    public function notifications(Request $request)
    {
        $user = auth()->user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return $this->failed(null, 'Driver', 'Driver not found', 404);
        }

        $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();

        return $this->success($notifications, 'Notifications retrieved successfully');
    }

    //readNotifications
    public function readNotifications(Request $request)
    {
        $user = auth()->user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return $this->failed(null, 'Driver', 'Driver not found', 404);
        }

        $notificationIds = $request->input('notification_ids', []);

        if (empty($notificationIds)) {
            return $this->failed(null, 'No notification IDs provided', 400);
        }

        // Mark specified notifications as read
        $user->unreadNotifications()
            ->whereIn('id', $notificationIds)
            ->update(['read_at' => now()]);

        return $this->success(null, 'Notifications marked as read successfully');
    }
}
