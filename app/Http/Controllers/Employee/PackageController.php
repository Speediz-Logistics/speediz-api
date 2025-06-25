<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\PackageRequest;
use App\Http\Requests\Employee\PackageStoreRequest;
use App\Http\Resources\Employee\PackageResource;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\DeliveryFee;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\Package;
use App\Models\PackageType;
use App\Models\Shipment;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use Dom\DocumentType;
use Illuminate\Http\Request;
use function Symfony\Component\String\b;

class PackageController extends Controller
{
    use BaseApiResponse;

    //index all packages with filter
    public function index(Request $request)
    {
        // Get all packages with filter
        $packages = Package::query();

        if ($request->has('status')) {
            $packages->where('status', $request->input('status'));
        }

        if ($request->has('vendor_id')) {
            $packages->where('vendor_id', $request->input('vendor_id'));
        }

        if ($request->has('date')) {
            $packages->whereBetween('created_at', $request->input('date'));
        }

        // Order by driver_id with NULL values first
        $packages->orderByRaw('CASE WHEN driver_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('driver_id');

        //order by status = pending
        $packages->orderByRaw('CASE WHEN status = "pending" THEN 0 ELSE 1 END')
            ->orderBy('status');

        // Add pagination
        $perPage = $request->input('per_page', 15); // Default to 15 items per page
        $paginatedPackages = $packages->paginate($perPage);

        return $this->success($paginatedPackages, 'Package List retrieved successfully');
    }

    //show
    public function show($id)
    {
        // Get package by id
        $package = Package::find($id);

        //load relationships
        $package->load('vendor', 'customer', 'driver', 'location', 'shipment', 'invoice', 'deliveryTracking', 'branch', 'package_type');

        $delivery_fees = DeliveryFee::query()->latest()->first();
        //price_khr is from package price * currencies table exchange_rate
        $currencies = Currency::query()->latest()->first();
        $package->price_khr = (float) $package->price * $currencies->exchange_rate;
        $package->delivery_fee = $delivery_fees->fee;

        if (!$package) {
            return $this->failed(null, 'Package not found', 'Package not found', 404);
        }

        return $this->success(PackageResource::make($package), 'Package retrieved successfully');
    }

    public function search(Request $request)
    {
        //validate request
        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required',
        ]);

        $key = $request->input('key');
        $data = null;
        switch ($key) {
            case 'vendor':
                $data = $this->searchVendor($request);
                break;
            case 'customer':
                $data = $this->searchCustomer($request);
                break;
            case 'branch':
                $data = $this->searchBranch($request);
                break;
            case 'driver':
                $data = $this->searchDriver($request);
                break;
            case 'currency':
                $data = $this->convertCurrency($request);
                break;
                //package_type
            case 'package_type':
                $data = $this->searchPackageType($request);
                break;
            default:
                $data = 404;

        }

        return $this->success($data, 'Package retrieved successfully');
    }

    public function searchPackageType(Request $request)
    {
        // Get document type by name
        $documentType = PackageType::where('name', 'like', '%' . $request->input('value') . '%')->first();

        if (!$documentType) {
            return null;
        }

        return $documentType;
    }

    //get vendor by search phone number
    public function searchVendor(Request $request)
    {
        // Get vendor by phone number
        $vendor = Vendor::where('contact_number', $request->input('value'))->first();

        if (!$vendor) {
            return null;
        }

        $vendor['name'] = $vendor->first_name . ' ' . $vendor->last_name;

        return $vendor;
    }

    //search customer by phone number
    public function searchCustomer(Request $request)
    {
        // Get customer by phone number
        $customer = Customer::where('phone', $request->input('value'))->first();

        if (!$customer) {
            return null;
        }

        return $customer;
    }

    //search branch by name
    public function searchBranch(Request $request)
    {
        // Get branch by name
        $branch = Branch::where('id', $request->input('value'))->first();

        if (!$branch) {
            return null;
        }

        return $branch;
    }

    //convert currency
    public function convertCurrency(Request $request)
    {
        // Get currency by id
        $currency = Currency::query()->latest()->first();

        if (!$currency) {
            return null;
        }

        // Convert amount to KHR
        $convertedAmount = $request->input('value') * $currency->exchange_rate;

        return ['price_khr' => $convertedAmount];
    }

    //search driver by phone number
    public function searchDriver(Request $request)
    {
        // Get driver by phone number
        $driver = Driver::where('contact_number', $request->input('value'))->first();

        if (!$driver) {
            return null;
        }

        $driver['name'] = $driver->first_name . ' ' . $driver->last_name;

        return $driver;
    }

    //update
    public function update(PackageRequest $request, $id)
    {
        // Get package by id
        $package = Package::find($id);

        if (!$package) {
            return $this->failed(null, 'Package not found', 'Package not found', 404);
        }

        $vendor = Vendor::where('id', $request->input('sender_id'))->first();
        $customer = Customer::where('phone', $request->input('receiver_phone'))->first();
        $branch = Branch::where('phone', $request->input('branch_phone'))->first();
        $driver = Driver::where('contact_number', $request->input('driver_phone'))->first();
        $package_type = PackageType::where('name', $request->input('package_type_name'))->first();

        if (!$package_type) {
            //create
            $package_type = new PackageType();
            $package_type->name = $request->input('package_type_name');
            $package_type->description = $request->input('package_type_description');
            $package_type->save();
        }

        $package->vendor_id = $vendor->id;
        $package->customer_id = $customer->id;
        $package->branch_id = $branch->id;
        $package->driver_id = $driver->id;
        $package->package_type_id = $package_type->id;
        $package->price = $request->input('package_price');
        $package->note = $request->input('note');
        $package->save();

        return $this->success($package, 'Package updated successfully');
    }

    //store
    public function store(PackageStoreRequest $request)
    {
        $vendor = Vendor::where('id', $request->input('sender_id'))->first();
        $customer = Customer::where('phone', $request->input('receiver_phone'))->first();
        $branch = Branch::where('phone', $request->input('branch_phone'))->first();
        $driver = Driver::where('contact_number', $request->input('driver_phone'))->first();
        $package_type = PackageType::where('name', $request->input('package_type_name'))->first();

        if (!$package_type) {
            //create
            $package_type = new PackageType();
            $package_type->name = $request->input('package_type_name');
            $package_type->description = $request->input('package_type_description');
            $package_type->save();
        }

        //create customer if not exist
        if (!$customer) {
            $customer = new Customer();
            $customer->first_name = $request->input('receiver_name');
            $customer->last_name = '';
            $customer->phone = $request->input('receiver_phone');
            $customer->save();
        }

        $package = Package::query()->create([
            'number' => random_int(100000, 999999),
            'name' => 'Package' . time(),
            'slug' => 'package' . time(),
            'price' => $request->input('package_price'),

            'vendor_id' => $vendor->id,
            'customer_id' => $customer ? $customer->id : null,
            'branch_id' => $branch->id,
            'driver_id' => $driver->id,
            'package_type_id' => $package_type->id,
            'note' => $request->input('note'),
            'zone' => 'A'
        ]);

        $invoice = Invoice::query()->create([
            'vendor_invoice_id' => $package->id,
            'customer_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'employee_id' => auth()->user()->id,
            'driver_id' => $driver->id,
            'package_id' => $package->id,
            'number' => 'INV-' . time() . '-' . $package->id,
            'date' => now(),
            'total' => $request->input('package_price'),
            'status' => 'pending',
            'note' => $request->input('note'),
        ]);

        $shipment = Shipment::query()->create([
            'package_id' => $package->id,
            'number' => 'SHIP-' . time() . '-' . $package->id,
            'type' => 'standard',
            'description' => $request->input('note'),
            'date' => now(),
            'delivery_fee' => 1.5, // Assuming delivery fee is 0 for now
            'status' => 'pending',
        ]);

        //locations
        $location = Location::query()->create([
            'location' => $request->input('receiver_address'),
            'lat' => $request->input('receiver_lat'),
            'lng' => $request->input('receiver_lng'),
        ]);

        // Attach the location to the package
        $package->location()->associate($location);
        $package->save();

        $package->shipment()->associate($shipment);
        $package->save();
        // Attach the invoice to the package
        $package->invoice()->associate($invoice);
        $package->save();

        return $this->success($package, 'Package created successfully');
    }

}
