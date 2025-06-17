<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ConstUserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\VendorResource;
use App\Mail\VendorRegistrationMail;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use App\Traits\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class VendorUserController extends Controller
{
    use BaseApiResponse, UploadImage;
    //index
    public function index()
    {
        $per_page = request()->query('per_page', config('pagination.per_page', 10));
        $search = request()->query('search'); // search by customer phone
        $date = request()->query('date'); // search by date
        $status = request()->query('status');

        $vendors = Vendor::query()
            ->with(['user'])
            ->when($search, fn($query, $search) => $query->where('id', $search))
            ->when($date, fn($query, $date) => $query->whereDate('created_at', $date))
            ->when($status, fn($query, $status) => $query->whereHas('user', fn($query) => $query->where('account_status', $status)))
            ->paginate($per_page);

        return $this->success([
            'data' => VendorResource::collection($vendors),
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
        ], 'Vendors', 'Vendors data fetched successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'business_name' => 'nullable|string',
            'business_type' => 'nullable|string',
            'business_description' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'address' => 'nullable|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'image' => 'nullable',
            'bank_name' => 'nullable|string',
            'bank_number' => 'nullable|string',

            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        //update image
        $image = null;
        if ($request->hasFile('image')) {
            $image = $this->upload($request);
        }

        $password = Hash::make($request->password);

        //create user
        $user = User::create([
            'role' => ConstUserRole::VENDOR,
            'email' => $request->email,
            'password' => $password,
            'account_status' => 1,
        ]);

        // Create the vendor and the associated user
        $vendor = Vendor::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'business_description' => $request->business_description,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'contact_number' => $request->contact_number,
            'image' => $image ?? '',
            'bank_name' => $request->bank_name,
            'bank_number' => $request->bank_number,
            'user_id' => $user->id,
        ]);

        Mail::to($user->email)->send(new VendorRegistrationMail($request->password));

        return $this->success($vendor, 'Vendor Created', 'Vendor created successfully');
    }

    //update
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'business_name' => 'nullable|string',
            'business_type' => 'nullable|string',
            'business_description' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'address' => 'nullable|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'image' => 'nullable',
            'bank_name' => 'nullable|string',
            'bank_number' => 'nullable|string',
            'status' => 'nullable|integer',
            'password' => 'nullable|string|min:6',
        ]);

        $vendor = Vendor::find($id);

        if (!$vendor) {
            return $this->failed(null, 'Vendor Not Found', 'Vendor not found', 404);
        }

        $image = $request->image ?? null;
        if ($request->hasFile('image')) {
            $image = $this->updateImage($request, $vendor);
        }

        $password = Hash::make($request->password);

        if ($request->password) {
            $vendor->user->update([
                'password' => $password,
            ]);
        }

        logger($request->status);
        $vendor->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'business_description' => $request->business_description,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'contact_number' => $request->contact_number,
            'image' => $image ?? '',
            'bank_name' => $request->bank_name,
            'bank_number' => $request->bank_number,
        ]);

        //get user
        $user = $vendor->user;
        $user->update([
            'account_status' => $request->status,
        ]);

        Mail::to($user->email)->send(new VendorRegistrationMail($request->password));

        return $this->success(VendorResource::make($vendor), 'Vendor Updated', 'Vendor updated successfully');
    }

    //show
    public function show($id)
    {
        $vendor = Vendor::with(['user'])->find($id);
        if (!$vendor) {
            return $this->failed(null, 'Vendor Not Found', 'Vendor not found', 404);
        }
        return $this->success(VendorResource::make($vendor), 'Vendor', 'Vendor data fetched successfully');
    }

    //destroy
    public function destroy($id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return $this->failed(null, 'Vendor Not Found', 'Vendor not found', 404);
        }

        //delete user
        $vendor->user->delete();

        //delete vendor
        $vendor->delete();

        return $this->success(null, 'Vendor Deleted', 'Vendor deleted successfully');
    }
}
