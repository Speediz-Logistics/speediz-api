<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ConstUserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\EmployeeResource;
use App\Http\Resources\Admin\VendorResource;
use App\Mail\VendorRegistrationMail;
use App\Models\Employee;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use App\Traits\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmployeeUserController extends Controller
{
    use BaseApiResponse, UploadImage;
    public function index()
    {
        $per_page = request()->query('per_page', config('pagination.per_page', 10));
        $search = request()->query('search'); // search by customer phone
        $date = request()->query('date'); // search by date
        $status = request()->query('status');

        $employees = Employee::query()
            ->with(['user'])
            ->when($search, fn($query, $search) => $query->where('id', $search))
            ->when($date, fn($query, $date) => $query->whereDate('created_at', $date))
            ->when($status, fn($query, $status) => $query->whereHas('user', fn($query) => $query->where('account_status', $status)))
            ->paginate($per_page);

        return $this->success([
            'data' => EmployeeResource::collection($employees),
            'paginate' => [
                'current_page' => $employees->currentPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
                'last_page' => $employees->lastPage(),
                'first_page_url' => $employees->url(1),
                'last_page_url' => $employees->url($employees->lastPage()),
                'next_page_url' => $employees->nextPageUrl(),
                'prev_page_url' => $employees->previousPageUrl(),
            ],
        ], 'Employees', 'Employees data fetched successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'contact_number' => 'nullable|string',
            'image' => 'nullable',

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
            'role' => ConstUserRole::EMPLOYEE,
            'email' => $request->email,
            'password' => $password,
            'account_status' => 1,
        ]);

        // Create the vendor and the associated user
        $vendor = Employee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'contact_number' => $request->contact_number,
            'image' => $image ?? '',
            'user_id' => $user->id,
        ]);

        Mail::to($user->email)->send(new VendorRegistrationMail($request->password));

        return $this->success($vendor, 'Employee Created', 'Employee created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'contact_number' => 'nullable|string',
            'image' => 'nullable',
            'status' => 'nullable|integer',
            'password' => 'nullable|string|min:6',
        ]);

        $vendor = Employee::find($id);

        if (!$vendor) {
            return $this->failed(null, 'Employee Not Found', 'Employee not found', 404);
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
            'contact_number' => $request->contact_number,
            'image' => $image ?? '',
        ]);

        //get user
        $user = $vendor->user;
        $user->update([
            'account_status' => $request->status,
        ]);

        Mail::to($user->email)->send(new VendorRegistrationMail($request->password));

        return $this->success(EmployeeResource::make($vendor), 'Employee Updated', 'Employee updated successfully');
    }

    public function show($id)
    {
        $vendor = Employee::with(['user'])->find($id);
        if (!$vendor) {
            return $this->failed(null, 'Employee Not Found', 'Employee not found', 404);
        }
        return $this->success(EmployeeResource::make($vendor), 'Employee', 'Employee data fetched successfully');
    }

    //destroy
    public function destroy($id)
    {
        $vendor = Employee::find($id);

        if (!$vendor) {
            return $this->failed(null, 'Employee Not Found', 'Employee not found', 404);
        }

        //delete user
        $vendor->user->delete();

        //delete vendor
        $vendor->delete();

        return $this->success(null, 'Employee Deleted', 'Employee deleted successfully');
    }
}
