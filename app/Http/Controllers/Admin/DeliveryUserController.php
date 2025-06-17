<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ConstUserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DeliveryResource;
use App\Http\Resources\Admin\VendorResource;
use App\Http\Resources\Vendor\DriverResource;
use App\Mail\VendorRegistrationMail;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use App\Traits\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DeliveryUserController extends Controller
{
    use BaseApiResponse, UploadImage;
    //index
    public function index()
    {
        $per_page = request()->query('per_page', config('pagination.per_page', 10));
        $search = request()->query('search'); // search by customer phone

        $vendors = Driver::query()
            ->with(['user'])
            //search id or name
            ->when($search, fn($query, $search) => $query->where('id', $search))
            ->paginate($per_page);

        return $this->success([
            'data' => DeliveryResource::collection($vendors),
            'paginate' => [
                'current_page' => $vendors->currentPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
                'last_page' => $vendors->lastPage(),
                'first_page_url' => $vendors->url(1),
                'last_page_url' => $vendors->url($vendors->lastPage()),
                'next_page_url' => $vendors->nextPageUrl(),
                'prev_page_url' => $vendors->previousPageUrl(),
            ],
        ], 'Drivers', 'DriversVendorResource data fetched successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'driver_type' => 'nullable|string',
            'driver_description' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'zone' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'telegram_contact' => 'nullable|string',
            'image' => 'nullable',
            'bank_name' => 'nullable|string',
            'bank_number' => 'nullable|string',
            'cv' => 'nullable',
            'address' => 'nullable|string',

            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        //update image
        $image = null;
        if ($request->hasFile('image')) {
            $image = $this->upload($request);
        }

        //cv image
        $cv = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cv', 'public');

            // Generate the correct file URL, ensuring it does not contain extra base URLs
            $cv = Storage::url($cvPath);
        }

        $password = Hash::make($request->password);

        //create user
        $user = User::create([
            'role' => ConstUserRole::DELIVERY,
            'email' => $request->email,
            'password' => $password,
            'account_status' => 1,
        ]);

        // Create the vendor and the associated user
        $vendor = Driver::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'driver_type' => $request->driver_type,
            'driver_description' => $request->driver_description,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'zone' => $request->zone,
            'contact_number' => $request->contact_number,
            'telegram_contact' => $request->telegram_contact,
            'image' => $image ?? '',
            'bank_name' => $request->bank_name,
            'bank_number' => $request->bank_number,
            'cv' => $cv ?? '',
            'address' => $request->address,
            'user_id' => $user->id,
        ]);

        Mail::to($user->email)->send(new VendorRegistrationMail($request->password));

        return $this->success($vendor, 'Driver Created', 'Driver created successfully');
    }

    //update
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'driver_type' => 'nullable|string',
            'driver_description' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'zone' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'telegram_contact' => 'nullable|string',
            'image' => 'nullable',
            'bank_name' => 'nullable|string',
            'bank_number' => 'nullable|string',
            'cv' => 'nullable',
            'address' => 'nullable|string',

            'password' => 'nullable|string|min:6',
            'status' => 'nullable|boolean',
        ]);

        $driver = Driver::find($id);

        if (!$driver) {
            return $this->failed(null, 'Driver Not Found', 'Driver not found', 404);
        }

        $image = $request->image ?? null;
        if ($request->hasFile('image')) {
            $image = $this->updateImage($request, $driver);
        }

        //cv image
        $cv = $request->cv ?? null;
        if ($request->hasFile('cv')) {
            // Save the file to the 'public' disk (which is typically the storage/app/public directory)
            $cvPath = $request->file('cv')->store('cv', 'public');

            // Generate the correct file URL, ensuring it does not contain extra base URLs
            $cv = Storage::url($cvPath);
        }

        $password = Hash::make($request->password);

        if ($request->password) {
            $driver->user->update([
                'password' => $password,
            ]);
        }

        $driver->user->update([
            'account_status' => $request->status,
        ]);

        $driver->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'driver_type' => $request->driver_type,
            'driver_description' => $request->driver_description,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'zone' => $request->zone,
            'contact_number' => $request->contact_number,
            'telegram_contact' => $request->telegram_contact,
            'image' => $image ?? '',
            'bank_name' => $request->bank_name,
            'bank_number' => $request->bank_number,
            'cv' => $cv ?? '',
            'address' => $request->address,
        ]);

        //get user
        $user = $driver->user;

        Mail::to($user->email)->send(new VendorRegistrationMail($request->password));

        return $this->success(DriverResource::make($driver), 'Driver Updated', 'Driver updated successfully');
    }

    //show
    public function show($id)
    {
        $vendor = Driver::with(['user'])->find($id);

        if (!$vendor) {
            return $this->failed(null, 'Driver Not Found', 'Driver not found', 404);
        }

        //get user data from vendor
        $vendor->user;

        return $this->success(DriverResource::make($vendor), 'Driver', 'Driver data fetched successfully');
    }

    //destroy
    public function destroy($id)
    {
        $vendor = Driver::find($id);

        if (!$vendor) {
            return $this->failed(null, 'Driver Not Found', 'Driver not found', 404);
        }

        //delete user
        $vendor->user->delete();

        //delete vendor
        $vendor->delete();

        return $this->success(null, 'Driver Deleted', 'Driver deleted successfully');
    }
}
