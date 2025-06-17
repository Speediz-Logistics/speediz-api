<?php

namespace App\Http\Controllers\Vendor;

use App\Constants\ConstInvoiceStatus;
use App\Constants\ConstPackageStatus;
use App\Http\Resources\Vendor\PackageResource;
use App\Http\Resources\Vendor\PackageShowResource;
use App\Models\Customer;
use App\Models\DeliveryTracking;
use App\Models\Driver;
use App\Models\Location;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use App\Traits\UploadImage;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Http\Controllers\Controller;

class PackageController extends Controller
{
    use BaseApiResponse, UploadImage;
    /**
     * Display a listing of the packages with pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->query('limit', 15); // Default items per page is 15
        $date = $request->query('date');
        $search = $request->query('search');
        //location filter
        $location = $request->query('location');

        // Get the current user's vendor_id from the Vendor table
        $user = auth()->user();
        $vendor = Vendor::where('user_id', $user->id)->first();

        if (!$vendor) {
            return $this->failed(null,'Vendor not found for the current user.', 'No vendor associated with the current user.');
        }

        $packagesQuery = Package::query()
            ->when($search, function ($query, $search) {
                $query->where('number', '=', $search);
            })
            //status in pending and in_transit
            ->whereIn('status', ['pending', 'in_transit'])
            ->with([
                'vendor',
                'shipment',
                'invoice',
                'location',
                'customer'
            ])
            ->where('vendor_id', $vendor->id);

        //filter by location that location is related to the package
        if ($location) {
            $packagesQuery->whereHas('location', function ($query) use ($location) {
                $query->where('location', 'like', '%' . $location . '%');
            });
        }

        if ($date) {
            $packagesQuery->whereDate('created_at', $date);
        }

        $packages = $packagesQuery->paginate($limit);

        $data = [
            'packages' => PackageResource::collection($packages),
            'amount' => $packagesQuery->sum('price'),
            'total' => $packages->total(),
            'per_page' => $packages->perPage(),
            'current_page' => $packages->currentPage(),
            'last_page' => $packages->lastPage(),
            'from' => $packages->firstItem(),
            'to' => $packages->lastItem(),
            'next_page_url' => $packages->nextPageUrl(),
            'prev_page_url' => $packages->previousPageUrl(),
            'path' => $packages->path(),
        ];

        return $this->success(
            $data,
            'Packages Retrieved',
            'Packages fetched successfully with pagination.'
        );
    }

    public function history(Request $request)
    {
        $limit = $request->query('limit', 15); // Default items per page is 15
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');
        //location filter
        $location = $request->query('location');

        // Get the current user's vendor_id from the Vendor table
        $user = auth()->user();
        $vendor = Vendor::where('user_id', $user->id)->first();

        if (!$vendor) {
            return $this->failed(null,'Vendor not found for the current user.', 'No vendor associated with the current user.');
        }

        $packagesQuery = Package::query()
            ->when($search, function ($query, $search) {
                $query->where('number', '=', $search);
            })
            //status not in pending and in_transit
            ->whereNotIn('status', ['pending', 'in_transit'])
            ->with([
                'vendor',
                'shipment',
                'invoice',
                'location',
                'customer'
            ])
            ->where('vendor_id', $vendor->id);

        //filter by location that location is related to the package
        if ($location) {
            $packagesQuery->whereHas('location', function ($query) use ($location) {
                $query->where('location', 'like', '%' . $location . '%');
            });
        }

        if ($startDate && $endDate) {
            $packagesQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $packages = $packagesQuery->paginate($limit);

        logger($packages);
        $data = [
            'packages' => PackageResource::collection($packages),
            'amount' => $packagesQuery->sum('price'),
            'total' => $packages->total(),
            'per_page' => $packages->perPage(),
            'current_page' => $packages->currentPage(),
            'last_page' => $packages->lastPage(),
            'from' => $packages->firstItem(),
            'to' => $packages->lastItem(),
            'next_page_url' => $packages->nextPageUrl(),
            'prev_page_url' => $packages->previousPageUrl(),
            'path' => $packages->path(),
        ];

        return $this->success(
            $data,
            'Packages Retrieved',
            'Packages fetched successfully with pagination.'
        );
    }

    /**
     * Store a newly created package in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'number' => 'required|string',
            'name' => 'required|string|max:255',
            'slug' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'zone' => 'nullable|string|max:255',

            'Customer_first_name' => 'nullable|string|max:255',
            'Customer_last_name' => 'nullable|string|max:255',
            'Customer_phone' => 'nullable|string|max:15',

            'location' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',

            'status' => 'nullable|string',
        ]);

        if (Package::where('number', $validatedData['number'])->exists()) {
            return $this->failed($validatedData['number'], 'Package Number Already Exists', 'The package number already exists.', 400);
        }

        // Check if the image is uploaded
        $image = null;
        if ($request->hasFile('image')) {
            $image = $this->upload($request);
        }

        // Check if customer exists or create a new one
        $customer = null;
        if (!empty($validatedData['Customer_phone']) && !empty($validatedData['Customer_first_name']) && !empty($validatedData['Customer_last_name'])) {
            $customer = Customer::firstOrCreate(
                ['phone' => $validatedData['Customer_phone']],
                [
                    'first_name' => $validatedData['Customer_first_name'] ?? null,
                    'last_name' => $validatedData['Customer_last_name'] ?? null,
                ]
            );
        }

        // Create location if provided
        $locationData = array_filter([
            'location' => $validatedData['location'] ?? null,
            'lat' => $validatedData['lat'] ?? null,
            'lng' => $validatedData['lng'] ?? null,
        ]);

        $location = !empty($locationData) ? Location::create($locationData) : null;

        $user = auth()->user();
        $vendor = Vendor::where('user_id', $user->id)->first();

        // Create package
        $package = Package::create([
            'number' => $validatedData['number'],
            'name' => $validatedData['name'],
            'slug' => $validatedData['slug'],
            'price' => $validatedData['price'],
            'description' => $validatedData['description'] ?? null,
            'image' => $image ?? null,
            'zone' => $validatedData['zone'] ?? null,
            'vendor_id' => $vendor->id,
            'customer_id' => $customer->id,
            'location_id' => $location->id,
            'status' => $validatedData['status'] ?? ConstPackageStatus::PENDING,
        ]);

        //create invoice for the package
        $package->invoice()->create([
            'customer_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'package_id' => $package->id,
            'number' => 'INV-' . $package->number,
            'total' => $package->price,
            'date' => now(),
            'status' => ConstInvoiceStatus::UNPAID,
        ]);

        return $this->success(
            $package,
            'Package Created',
            'The package has been created successfully.',
            201
        );
    }

    /**
     * Display the specified package.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = auth()->user();
        $vendor = Vendor::where('user_id', $user->id)->first();

        if (!$vendor) {
            return $this->failed(
                null,
                'Vendor Not Found',
                'No vendor associated with the current user.',
                404
            );
        }

        $package = Package::query()
            ->with(['vendor', 'shipment', 'invoice', 'location', 'customer'])
            ->where('vendor_id', $vendor->id) // Ensure the package belongs to this vendor
            ->find($id);

        if (!$package) {
            return $this->failed(
                null,
                'Package Not Found',
                'The requested package does not exist or does not belong to your vendor.',
                404
            );
        }

        if ($package->invoice) {
            $driver = Driver::find($package->invoice->driver_id);
            if ($driver) {
                $package->driver = $driver;
            }
        }

        return $this->success(
            PackageShowResource::make($package),
            'Package Retrieved',
            'The package details have been retrieved successfully.'
        );
    }

    /**
     * Update the specified package in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'number' => 'required|string',
            'name' => 'required|string|max:255',
            'slug' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'zone' => 'nullable|string|max:255',

            'Customer_first_name' => 'nullable|string|max:255',
            'Customer_last_name' => 'nullable|string|max:255',
            'Customer_phone' => 'nullable|string|max:15',

            'location' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',

            'status' => 'nullable|string',
        ]);

        // Check if the package exists
        $package = Package::find($id);
        if (!$package) {
            return $this->failed($id, 'Package Not Found', 'The package with the given ID was not found.', 404);
        }

        // Check if the package number is already used by another package
        if (Package::where('number', $validatedData['number'])->where('id', '!=', $id)->exists()) {
            return $this->failed($validatedData['number'], 'Package Number Already Exists', 'The package number already exists for another package.', 400);
        }

        // Handle image upload
        $image = $package->image; // Keep the existing image if no new image is uploaded
        if ($request->hasFile('image')) {
            $image = $this->upload($request);
        }

        // Handle customer information
        $customer = $package->customer;
        if (!empty($validatedData['Customer_phone']) && !empty($validatedData['Customer_first_name']) && !empty($validatedData['Customer_last_name'])) {
            $customer = Customer::updateOrCreate(
                ['phone' => $validatedData['Customer_phone']],
                [
                    'first_name' => $validatedData['Customer_first_name'] ?? null,
                    'last_name' => $validatedData['Customer_last_name'] ?? null,
                ]
            );
        }

        // Update location if data is provided
        $locationData = array_filter([
            'location' => $validatedData['location'] ?? null,
            'lat' => $validatedData['lat'] ?? null,
            'lng' => $validatedData['lng'] ?? null,
        ]);
        $location = $package->location;
        if (!empty($locationData)) {
            $location->update($locationData);
        }

        // Retrieve vendor from the logged-in user
        $user = auth()->user();
        $vendor = Vendor::where('user_id', $user->id)->first();

        // Update the package with the validated data
        $package->update([
            'number' => $validatedData['number'],
            'name' => $validatedData['name'],
            'slug' => $validatedData['slug'],
            'price' => $validatedData['price'],
            'description' => $validatedData['description'] ?? null,
            'image' => $image ?? null,
            'zone' => $validatedData['zone'] ?? null,
            'vendor_id' => $vendor->id,
            'customer_id' => $customer->id,
            'location_id' => $location->id,
            'status' => $validatedData['status'] ?? null,
        ]);

        return $this->success(
            $package,
            'Package Updated',
            'The package has been updated successfully.',
            200
        );
    }


    /**
     * Remove the specified package from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $package = Package::find($id);

        if (!$package) {
            return $this->failed(
                null,
                'Package Not Found',
                'The package you are trying to delete does not exist.',
                404
            );
        }

        $package->delete();

        return $this->success(
            null,
            'Package Deleted',
            'The package has been deleted successfully.'
        );
    }

    //search by number
    public function search($identifier)
    {
        $user = auth()->user();
        $vendor = Vendor::where('user_id', $user->id)->first();

        if (!$vendor) {
            return $this->failed(
                null,
                'Vendor Not Found',
                'No vendor associated with the current user.',
                404
            );
        }

        // Check if the identifier is numeric (ID) or a package number (character)
        $query = Package::query()->with(['vendor', 'shipment', 'invoice', 'location', 'customer']);

        $query->where('number', $identifier); // Assuming 'package_number' is the field storing the character-based identifier

        $package = $query->where('vendor_id', $vendor->id)->first();

        if (!$package) {
            return $this->failed(
                null,
                'Package Not Found',
                'The requested package does not exist or does not belong to your vendor.',
                404
            );
        }

        if ($package->invoice) {
            $driver = Driver::find($package->invoice->driver_id);
            if ($driver) {
                $package->driver = $driver;
            }
        }

        return $this->success(
            PackageShowResource::make($package),
            'Package Retrieved',
            'The package details have been retrieved successfully.'
        );
    }

    //mapDetail
    public function map($id)
    {
        //get package location
        $tracking = Package::query()
            ->with(['location'])
            ->find($id);

        if (!$tracking) {
            return $this->failed(
                null,
                'Package Not Found',
                'The requested package does not exist.',
                404
            );
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


        return $this->success(
            $tracking,
            'Package Map Retrieved',
            'The package map details have been retrieved successfully.'
        );
    }
}
