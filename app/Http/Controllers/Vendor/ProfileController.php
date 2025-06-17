<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\ProfileResource;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use App\Traits\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use BaseApiResponse, UploadImage;
    //index
    public function index()
    {
        $user = auth()->user();
        $vendor = Vendor::query()->where('user_id', $user->id)->first();
        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        // Create an object for the resource
        $data = new ProfileResource([
            'vendor' => $vendor,
            'user' => $user
        ]);

        return $this->success($data, 'Vendor profile');
    }

    //update
    public function update(Request $request)
    {
        $user = auth()->user();
        $vendor = Vendor::query()->where('user_id', $user->id)->first();
        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        $validated = $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'business_name' => 'nullable|string',
            'dob' => 'nullable|date',
            'image' => 'nullable',
            'gender' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string',
        ]);

        $image = null;
        if ($request->hasFile('image')) {
            $image = $this->upload($request);
        }

        $vendor->update([
            'first_name' => $validated['first_name'] ?? $vendor->first_name,
            'last_name' => $validated['last_name'] ?? $vendor->last_name,
            'business_name' => $validated['business_name'] ?? $vendor->business_name,
            'dob' => $validated['dob'] ?? $vendor->dob,
            'image' => $image ?? $vendor->image,
            'gender' => $validated['gender'] ?? $vendor->gender,
            'address' => $validated['address'] ?? $vendor->address,
            'contact_number' => $validated['contact_number'] ?? $vendor->contact_number,
        ]);

        // Create an object for the resource
        $data = new ProfileResource([
            'vendor' => $vendor,
            'user' => $user
        ]);

        return $this->success($data, 'Vendor profile updated successfully');
    }

    //resetPassword
    public function resetPassword(Request $request)
    {
        $user = auth()->user();
        $vendor = Vendor::query()->where('user_id', $user->id)->first();
        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        $validated = $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|confirmed',
        ]);

        if (!Hash::check($validated['old_password'], $user->password)) {
            return $this->error('Old password is incorrect', 400);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return $this->success(null, 'Password updated successfully');
    }

    //logout
    public function logout()
    {
        // Revoke the token passport token
        auth()->user()->token()->revoke();

        return $this->success(null, 'Vendor logged out successfully');
    }
}
