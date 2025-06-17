<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Delivery\ExpressHistoryCollection;
use App\Http\Resources\Delivery\ExpressHistoryResource;
use App\Http\Resources\Delivery\ExpressShowHistoryResource;
use App\Models\Driver;
use App\Models\Package;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpressController extends Controller
{
    use BaseApiResponse;
    //index
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        $search = $request->query('search');
        $user = auth()->user();
        $driver = Driver::query()->where('user_id', $user->id)->first();

        // Ensure the driver exists
        if (!$driver) {
            return $this->failed('Driver not found', 404);
        }

        // Query packages grouped by updated_at
        $packages = Package::query()
            ->when($search, function ($query, $search) {
                return $query->where('number', 'like', '%' . $search . '%');
            })
            ->with([
                'vendor',
                'customer',
                'location',
                'driver',
                'shipment',
                'invoice',
            ])
            ->where('driver_id', $driver->id)
            ->orderBy('updated_at', 'desc') // Order by updated_at first
            ->get()
            ->groupBy(function ($item) {
                return $item->updated_at->format('M d, Y'); // Group by date of updated_at
            });

        // Paginate the grouped results manually
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $collection = collect($packages);
        $paginatedData = new LengthAwarePaginator(
            $collection->forPage($currentPage, $per_page),
            $collection->count(),
            $per_page,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return $this->success($paginatedData, 'Welcome to express delivery');
    }

    //show
    public function show($id)
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
            ->where('id', $id)
            ->first();

        // Ensure the package exists
        if (!$package) {
            return $this->failed('Package not found', 404);
        }

        return $this->success($package, 'Package details');
    }

    //history package status = COMPLETED and CANCELLED
    public function history(Request $request)
    {
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        $search = $request->query('search');
        $date = $request->query('date');
        $driver = Driver::where('user_id', auth()->id())->firstOrFail();

        $query = Package::where('driver_id', $driver->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->when($search, fn($query) => $query->where('number', 'like', "%$search%"))
            ->when($date, fn($query) => $query->whereDate('updated_at', $date))
            ->with(['vendor', 'customer', 'location', 'driver', 'shipment', 'invoice'])
            ->latest('updated_at')->paginate($perPage);

        return $this->success($query, 'Express delivery history');
    }

    //showHistory
    public function showHistory($id)
    {
        $driver = Driver::where('user_id', auth()->id())->firstOrFail();
        $package = Package::where('driver_id', $driver->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->with(['vendor', 'customer', 'location', 'driver', 'shipment', 'invoice'])
            ->findOrFail($id);

        return $this->success($package, 'Express delivery history details');
    }
}
