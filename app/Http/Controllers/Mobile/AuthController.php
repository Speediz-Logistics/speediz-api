<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use BaseApiResponse;

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
        $tokenResult = $user->createToken('mobileAuthToken');
        $token = $tokenResult->accessToken;
        $expiresAt = $tokenResult->token->expires_at;

        return $this->successAuth(
            $user,
            $token,
            "Successfully logged in",
            "Successfully logged in"
        );
    }
}
