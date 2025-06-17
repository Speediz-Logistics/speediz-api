<?php

namespace App\Http\Controllers\Vendor;

use App\Constants\ConstUserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use BaseApiResponse;

    public function show()
    {
        $users = User::query()
            ->whereNotIn('id', [auth()->guard('api')->user()->id])
            ->get();
        return response()->json([
            "users" => $users,
            "status" => 200,
        ]);
    }

    public function me()
    {
        return auth()->guard('api')->user();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json([
            "message" => "User deleted successfully!",
            "status" => 200
        ]);
    }

    public function getByChar($user_name)
    {
        $user = User::where('email', 'like', '%' . $user_name . '%')->get();

        if ($user->isNotEmpty()) {
            return response()->json([
                "users" => $user,
                "status" => 200,
            ]);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function register(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'account_status' => 'sometimes|string',
                // Vendor-specific fields
                'first_name' => 'required_if:role,vendor|string|max:255',
                'last_name' => 'required_if:role,vendor|string|max:255',
                'business_name' => 'required_if:role,vendor|string|max:255',
                'business_type' => 'required_if:role,vendor|string|max:255',
                'business_description' => 'nullable|string',
                'dob' => 'nullable|date',
                'gender' => 'nullable|string|in:male,female,other',
                'address' => 'nullable|string',
                'lat' => 'nullable|string',
                'lng' => 'nullable|string',
                'contact_number' => 'nullable|string|max:20',
                'image' => 'nullable|string|max:255',
                'bank_name' => 'nullable|string|max:255',
                'bank_number' => 'nullable|string|max:50',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Retrieve validated data
            $data = $validator->validated();

            // Hash the password
            $data['password'] = bcrypt($data['password']);

            // Default account status to active if not provided
            $data['account_status'] = $data['account_status'] ?? 1;

            // Create the user
            $user = User::create([
                'role' => ConstUserRole::VENDOR,
                'email' => $data['email'],
                'password' => $data['password'],
                'account_status' => $data['account_status'],
            ]);

            // Generate token
            $token = $user->createToken(env('APP_KEY'))->accessToken;

            // Create vendor profile if the role is vendor
            if ($user) {
                Vendor::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'business_name' => $data['business_name'],
                    'business_type' => $data['business_type'],
                    'business_description' => $data['business_description'] ?? null,
                    'dob' => $data['dob'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'address' => $data['address'] ?? null,
                    'lat' => $data['lat'] ?? null,
                    'lng' => $data['lng'] ?? null,
                    'contact_number' => $data['contact_number'] ?? null,
                    'image' => $data['image'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'bank_number' => $data['bank_number'] ?? null,
                    'user_id' => $user->id,
                ]);
            }

            return $this->successAuth(
                $user,
                $token,
                'Registration Successful',
                'You have successfully registered.',
                200
            );
        } catch (\Exception $e) {
            return $this->failed(
                $e->getMessage(),
                'Server Error',
                'An unexpected error occurred. Please try again later.',
                500
            );
        }
    }


    public function login(Request $request)
    {
        try {
            $data = $request->only(['email', 'password']);

            // Validate the request
            $validator = Validator::make($data, [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);

            if ($validator->fails()) {
                return $this->failed(
                    null,
                    'Validation Error',
                    'Please correct the input errors.',
                    400
                );
            }

            // Attempt authentication
            if (!auth()->attempt($data)) {
                return $this->failed(
                    null,
                    'Authentication Failed',
                    'Incorrect details. Please try again.',
                    401
                );
            }

            // Generate token for authenticated user
            $user = auth()->user();
            $token = $user->createToken(env('APP_KEY'))->accessToken;

            return $this->successAuth(
                $user,
                $token,
                'Login Successful',
                'You have successfully logged in.',
                200
            );
        } catch (\Exception $e) {
            return $this->failed(
                null,
                'Server Error',
                'An unexpected error occurred. Please try again later.',
                500
            );
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $data = $request->only(['role', 'email', 'password', 'account_status']);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $validator = Validator::make($data, [
            'email' => 'email',
            'password' => 'sometimes|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 400);
        }

        // Hash the password if provided
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        // Update user data
        $user->update($data);

        return response()->json([
            'user' => $user,
            'message' => 'Success',
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return $this->success(
            null,
            'Logout Successful',
            'You have successfully logged out.',
            200
        );
    }
}
