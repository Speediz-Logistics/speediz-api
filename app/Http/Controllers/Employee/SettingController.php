<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\DeliveryFee;
use App\Models\Employee;
use App\Traits\BaseApiResponse;
use App\Traits\UploadImage;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use BaseApiResponse, UploadImage;
    //index
    public function index()
    {
        //get all settings
        $user = auth()->user();
        if (!$user) {
            return $this->failed(null, 'Unauthorized', 'No User Unauthorized');
        }
        //get admin data
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return $this->failed(null, 'Unauthorized', 'No Employee Unauthorized');
        }

        $employee['email'] = $user->email;

        return $this->success($employee, 'Settings retrieved successfully');
    }

    //update
    public function update(Request $request)
    {
        // Authorization check
        $user = auth()->user();
        if (!$user) {
            return $this->failed(null, 'Unauthorized', 'No User Unauthorized');
        }

        // Get admin data
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return $this->failed(null, 'Unauthorized', 'No Employee Unauthorized');
        }

        // Validate request
        $validatedData = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'contact_number' => 'required|string',
            'image' => 'nullable|image', // Add image validation
        ]);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $validatedData['image'] = $this->updateImage($request, $employee);
        }

        // Update all validated fields
        $employee->update($validatedData);

        return $this->success($employee, 'Settings updated successfully');
    }
}
