<?php

namespace App\Http\Controllers\Delivery;

use App\Constants\ConstUserRole;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use BaseApiResponse;

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
            'image' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_number' => 'nullable|string|max:255',
            'telegram_contact' => 'nullable|string|max:255',
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

    public function me()
    {
        return auth()->guard('api')->user();
    }

}
