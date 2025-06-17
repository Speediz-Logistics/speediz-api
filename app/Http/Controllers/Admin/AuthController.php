<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ConstUserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use BaseApiResponse;
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = User::create([
            'role' => ConstUserRole::ADMIN,
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'account_status' => 1,
        ]);

        $user->sendEmailVerificationNotification();
        return $this->success($user,'Admin Account','Admin created successfully');
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
        $tokenResult = $user->createToken('AdminAuthToken');
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
        return $this->success($request->user(), 'Admin Profile');
    }

    //logout
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->success(null, 'Successfully logged out');
    }
}
