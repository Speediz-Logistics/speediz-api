<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\PackageType;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

class PackageOptionController extends Controller
{
    use BaseApiResponse;

    //switch options
    public function index(Request $request)
    {
        //validate request
        $request->validate([
            'key' => 'required|string|max:255',
            'search' => 'nullable',
        ]);

        $key = $request->input('key');
        $data = null;
        switch ($key) {
            case 'vendor':
                $data = $this->getVendorsOptions($request);
                break;
            case 'branch':
                $data = $this->getBranchOptions($request);
                break;
            case 'driver':
                $data = $this->getDriverOptions($request);
                break;
            //package_type
            case 'package_type':
                $data = $this->getPackageType($request);
                break;
            default:
                $data = 404;

        }

        return $this->success($data, 'Package retrieved successfully');
    }

    public function getVendorsOptions(Request $request)
    {
        $query = Vendor::query()
            ->select('id', 'business_name')
            ->orderBy('business_name'); // Changed from orderBy('id') to orderBy('name') for better UX

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('business_name', 'like', '%' . $searchTerm . '%')
                ->orWhere('id', 'like', '%' . $searchTerm . '%')
                ->orWhere('contact_number', 'like', '%' . $searchTerm . '%');
        }

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $paginatedVendors = $query->paginate($perPage);

        // Transform the items collection
        $transformedItems = collect($paginatedVendors->items())->map(function ($vendor) {
            return [
                'id' => $vendor->id,
                'name' => $vendor->business_name,
            ];
        })->toArray();

        return $transformedItems;
    }

    //getCustomersOptions
    public function getBranchOptions(Request $request)
    {
        $query = Branch::query()
            ->select('id', 'name', 'phone')
            ->orderBy('name'); // Changed from orderBy('id') to orderBy('name') for better UX

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('name', 'like', '%' . $searchTerm . '%')
            ->orWhere('id', 'like', '%' . $searchTerm . '%')
            ->orWhere('phone', 'like', '%' . $searchTerm . '%');
        }

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $paginatedVendors = $query->paginate($perPage);

        // Transform the items collection
        $transformedItems = collect($paginatedVendors->items())->map(function ($data) {
            return [
                'id' => $data->id,
                'name' => $data->name,
                'phone' => $data->phone,
            ];
        })->toArray();

        return $transformedItems;
    }

    public function getDriverOptions(Request $request)
    {
        $query = Driver::query()
            ->select('id', 'first_name', 'last_name', 'contact_number', 'telegram_contact')
            ->orderBy('id'); // Changed from orderBy('id') to orderBy('name') for better UX

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('first_name', 'like', '%' . $searchTerm . '%')
                ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                ->orWhere('id', 'like', '%' . $searchTerm . '%')
                ->orWhere('contact_number', 'like', '%' . $searchTerm . '%');
        }

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $paginatedVendors = $query->paginate($perPage);

        // Transform the items collection
        $transformedItems = collect($paginatedVendors->items())->map(function ($data) {
            return [
                'id' => $data->id,
                'name' => $data->first_name . ' ' . $data->last_name,
                'phone' => $data->contact_number,
                'telegram_contact' => $data->telegram_contact,
            ];
        })->toArray();

        return $transformedItems;
    }

    public function getPackageType(Request $request)
    {
        $query = PackageType::query()
            ->select('id', 'name', 'description')
            ->orderBy('name'); // Changed from orderBy('id') to orderBy('name') for better UX


        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('id', 'like', '%' . $searchTerm . '%');
        }

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $paginatedVendors = $query->paginate($perPage);

        // Transform the items collection
        $transformedItems = collect($paginatedVendors->items())->map(function ($data) {
            return [
                'id' => $data->id,
                'name' => $data->name,
                'description' => $data->description,
            ];
        })->toArray();

        return $transformedItems;
    }
}
