<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class UserAuthController extends Controller
{

    use BaseApiResponse;
    public function show()
    {
        $users = DB::table('users')
            ->whereNotIn('id', [auth()->guard('api')->user()->id])
            ->get();
        return response()->json([
            "users" => $users,
            "status" => 200,
        ]);
    }

    public function user()
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

        $user = User::where('name', 'like', '%' . $user_name . '%')->get();

        if ($user) {
            return response()->json([
                "users" => $user,
                "status" => 200,
            ]);
        } else {
            return response()->json(['error' => 'user not found'], 404);
        }
    }

    public function register(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'role' => 'nullable|string|max:15',
                'business_name' => 'nullable|string|max:255',
                'dob' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'address' => 'nullable|string|max:255',
                'contact_number' => 'nullable|string|max:20',
                'image' => 'nullable|file|image|max:2048', // Max 2MB
            ]);

            if ($validator->fails()) {
                return $this->failed(
                    $validator->errors(),
                    'Validation Error',
                    'Please correct the input errors.',
                    400
                );
            }

            $data = $validator->validated();

            // Format the date of birth (if provided)
            if (!empty($data['dob'])) {
                $data['dob'] = Carbon::parse($data['dob'])->format('Y-m-d'); // Convert to MySQL-compatible format
            }

            // Hash the password
            $data['password'] = bcrypt($request->password);

            // Handle image upload (Cloudinary)
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->getRealPath();
                $uploadedImage = Cloudinary::upload($imagePath)->getSecurePath();
                $data['image'] = $uploadedImage;
            } elseif ($request->image) {
                $imageBase64 = $request->image;
                $uploadedImage = Cloudinary::upload("data:image/png;base64," . $imageBase64)->getSecurePath();
                $data['image'] = $uploadedImage;
            }

            // Create the user
            $user = User::create($data);

            // Generate token
            $token = $user->createToken('API Token')->accessToken;

            return $this->successAuth(
                $user,
                $token,
                'Registration Successful',
                'You have successfully registered.',
                200
            );
        } catch (\Exception $e) {
            // Handle unexpected errors
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
            $data = $request->all();

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
            if (!auth()->attempt(['email' => $data['email'], 'password' => $data['password']])) {
                return $this->failed(
                    null,
                    'Authentication Failed',
                    'Incorrect details. Please try again.',
                    401
                );
            }

            // Generate token for authenticated user
            $user = auth()->user();
            $token = $user->createToken('API Token')->accessToken;

            return $this->successAuth(
                $user,
                $token,
                'Login Successful',
                'You have successfully logged in.',
                200
            );
        } catch (\Exception $e) {
            // Handle unexpected errors
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
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'name' => 'max:255',
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 400);
        }

        // if ($request->hasFile('image')) {
        //     $image = $request->file('image');
        //     $image_name = time() . '.' . $image->getClientOriginalExtension();
        //     $image->move(public_path('images/users/'), $image_name);
        //     $data['image'] = $image_name;
        //     // remove old image
        //     $old_image = public_path('images/users/' . $user->image);
        //     if (file_exists($old_image)) {
        //         @unlink($old_image);
        //     }
        // } else if ($request->image) {
        //     $base64_string = $request->image;
        //     $image = base64_decode($base64_string);

        //     $file_name = time() . '.' . 'png';
        //     $file_path = public_path('images/users/' . $file_name);
        //     file_put_contents($file_path, $image);
        //     $data['image'] = $file_name;
        //     // remove old image
        //     $old_image = public_path('images/users/' . $user->image);
        //     if (file_exists($old_image)) {
        //         @unlink($old_image);
        //     }
        // }

        // upload image to storage for Cloudinary
        if ($request->hasFile('image')) {

            //check if old image exist
            $old_image = Cloudinary::getImage($user->image);
            if ($old_image->getPublicId() != null) {
                Cloudinary::destroy($old_image->getPublicId());
            }

            //set image to response json
            $imagePath = $request->file('image')->getRealPath();
            $uploadedImage = Cloudinary::upload($imagePath)->getSecurePath();
            $data['image'] = $uploadedImage;
        } else if ($request->image) {
            //check if old image exist
            $old_image = Cloudinary::getImage($user->image);
            if ($old_image->getPublicId() != null) {
                Cloudinary::destroy($old_image->getPublicId());
            }
            //set image to response json
            $imageBase64 = $request->image;
            $uploadedImage = Cloudinary::upload("data:image/png;base64," . $imageBase64)->getSecurePath();
            $data['image'] = $uploadedImage;
        }

        if ($user) {
            $user->update($data);
            return response(['user' => $user, 'message' => 'Success'], 200);
        } else {
            return response(['message' => 'User not found']);
        }
    }


    public function logout()
    {
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return $this->success(
            null,
            'Logout Successful',
            'You have successfully logged out.',
            200
        );
    }
}
