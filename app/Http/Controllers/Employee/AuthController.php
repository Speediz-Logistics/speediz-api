<?php

namespace App\Http\Controllers\Employee;

use App\Constants\ConstUserRole;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Traits\BaseApiResponse;
use App\Traits\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use BaseApiResponse, UploadImage;

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'contact_number' => 'required|string',
            'image' => 'nullable|image',
        ]);
        $user = User::create([
            'role' => ConstUserRole::EMPLOYEE,
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'account_status' => 1,
        ]);

        $image = null;
        if ($request->hasFile('image')) {
            $image = $this->upload($request);
        }

        Employee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'contact_number' => $request->contact_number,
            'image' => $image ?? '',
            'user_id' => $user->id
        ]);

        $user->sendEmailVerificationNotification();
        return $this->success($user,'Employee Account','Admin created successfully');
    }

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

        // Generate token using Passport
        $tokenResult = $user->createToken('EmployeeAuthToken');
        $token = $tokenResult->accessToken;

        return $this->successAuth(
            $user,
            $token,
            "Successfully logged in",
            "Successfully logged in"
        );
    }

    //me
    public function me(Request $request)
    {
        return $this->success($request->user(), 'Employee Profile');
    }

    //logout
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->success(null, 'Successfully logged out');
    }
}
