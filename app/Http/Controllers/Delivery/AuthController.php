<?php

namespace App\Http\Controllers\Delivery;

use App\Constants\ConstUserRole;
use App\Traits\BaseApiResponse;
use App\Traits\UploadImage;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use BaseApiResponse, UploadImage;

    // Register a new driver
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',

            // Driver fields
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'driver_type' => 'required|string|max:255',
            'driver_description' => 'nullable|string',
            'dob' => 'required|date',
            'gender' => 'required|string|in:male,female',
            'zone' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'image' => 'nullable',
            'bank_name' => 'nullable|string|max:255',
            'bank_number' => 'nullable|string|max:255',
            'telegram_contact' => 'nullable|string|max:255',

            'nid' => 'nullable',
        ]);

        // Create User
        $user = User::create([
            'role' => ConstUserRole::DELIVERY,
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'account_status' => 1, // Default account status
        ]);

        // Dispatch email verification notification
        $user->sendEmailVerificationNotification();

        $image = null;
        // Handle image upload if provided
        if (isset($validatedData['image'])) {
            $image = $this->upload($request, 'image');
            $validatedData['image'] = $image;
        }

        $nidImage = null;
        // Handle nid upload if provided
        if (isset($validatedData['nid'])) {
            $nidImage = $this->upload($request, 'nid');
            $validatedData['nid'] = $nidImage;
        }

        // Create Driver
        $driver = Driver::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'driver_type' => $validatedData['driver_type'],
            'driver_description' => $validatedData['driver_description'] ?? null,
            'dob' => $validatedData['dob'],
            'gender' => $validatedData['gender'],
            'zone' => $validatedData['zone'],
            'contact_number' => $validatedData['contact_number'],
            'telegram_contact' => $validatedData['telegram_contact'] ?? null,
            'image' => $validatedData['image'] ?? null,
            'bank_name' => $validatedData['bank_name'] ?? null,
            'bank_number' => $validatedData['bank_number'] ?? null,
            'user_id' => $user->id,
            'nid' => $validatedData['nid'] ?? null,
        ]);

        return $this->success(
            ['driver' => $driver, 'user' => $user],
            "Successfully created Driver. Please verify your email.",
            "Successfully created Driver. Please verify your email."
        );
    }

    // Login a driver
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_token' => 'nullable|string',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        // Check if the user's email is verified
        if (is_null($user->email_verified_at)) {
            throw ValidationException::withMessages([
                'email' => ['Your email address is not verified. Please verify your email before logging in.'],
            ]);
        }

        if (isset($credentials['device_token'])) {
            $user->device_token = $credentials['device_token'];
            $user->save();
        }

        // Generate token using Passport
        $tokenResult = $user->createToken('DriverAuthToken');
        $token = $tokenResult->accessToken;
        $expiresAt = $tokenResult->token->expires_at;

        return $this->successAuth(
            $user,
            $token,
            "Successfully logged in",
            "Successfully logged in"
        );
    }

    // Logout a driver
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    //me
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->failed(null, 'User', 'User not authenticated', 401);
        }
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return $this->failed(null, 'Driver', 'Driver not found', 404);
        }

        return $this->success(
            [
                'user' => $user,
                'driver' => $driver
            ],
            "Successfully fetched authenticated driver",
            "Successfully fetched authenticated driver"
        );
    }

    //updateProfile
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->failed(null, 'User', 'User not authenticated', 401);
        }

        $driver = Driver::where('user_id', $user->id)->first();
        if (!$driver) {
            return $this->failed(null, 'Driver', 'Driver not found', 404);
        }

        // --------------------------
        // 1. VALIDATION
        // --------------------------
        $validated = $request->validate([
            'first_name'         => 'sometimes|string|max:255',
            'last_name'          => 'sometimes|string|max:255',
            'driver_type'        => 'sometimes|string|max:255',
            'driver_description' => 'sometimes|string|max:500',
            'dob'                => 'sometimes',
            'gender'             => 'sometimes',
            'zone'               => 'sometimes|string|max:255',
            'contact_number'     => 'sometimes|string|max:20',

            'image'              => 'sometimes',
            'telegram_contact'   => 'sometimes|string|max:255',
            'nid'                 => 'sometimes',
            'address'            => 'sometimes|string|max:255',
        ]);

        // --------------------------
        // 2. UPDATE USER TABLE
        // --------------------------
        $user->update([
            'first_name'     => $validated['first_name'] ?? $user->first_name,
            'last_name'      => $validated['last_name'] ?? $user->last_name,
            'phone'          => $validated['contact_number'] ?? $user->phone,
            'telegram'       => $validated['telegram_contact'] ?? $user->telegram,
            'address'        => $validated['address'] ?? $user->address,
        ]);

        // --------------------------
        // 3. HANDLE FILE UPLOADS
        // --------------------------

        // Upload profile image
        if ($request->hasFile('image')) {
            $imagePath = $this->upload($request, 'driver_image');
            $driver->image = $imagePath;
        }

        // Upload nid
        if ($request->hasFile('nid')) {
            $nidPath = $this->upload($request, 'nid');
            $driver->nid = $nidPath;
        }

        // --------------------------
        // 4. UPDATE DRIVER TABLE
        // --------------------------
        $driver->update([
            'driver_type'        => $validated['driver_type'] ?? $driver->driver_type,
            'driver_description' => $validated['driver_description'] ?? $driver->driver_description,
            'dob'                => $validated['dob'] ?? $driver->dob,
            'gender'             => $validated['gender'] ?? $driver->gender,
            'zone'               => $validated['zone'] ?? $driver->zone,
        ]);

        $driver->save();

        return $this->success([
            'user' => $user,
            'driver' => $driver
        ], 'Profile updated successfully');
    }

    //resetPassword
    public function resetPassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Check if current password matches
        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->failed(null, 'Password', 'Current password is incorrect', 400);
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return $this->success(null, 'Password updated successfully', 'Password updated successfully');
    }

    //resetPasswordToken
    public function resetPasswordToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(null, __('passwords.reset'), __('passwords.reset'));
        }

        return $this->error(null, __('passwords.token'), 400);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(
                null,
                __('passwords.sent'),
                __('passwords.sent')
            );
        }

        return $this->error(
            null,
            __('passwords.user'),
            404
        );
    }



}
