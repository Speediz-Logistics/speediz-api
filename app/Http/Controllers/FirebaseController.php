<?php

namespace App\Http\Controllers;

use App\Traits\FCM;
use App\Traits\UploadImage;
use Illuminate\Http\Request;

class FirebaseController extends Controller
{
    use FCM, UploadImage;

    public function sendTestNotification(Request $request)
    {
        if (empty($request->all())) {
            return response()->json([
                "success" => false,
                "message" => "No data provided",
            ]);
        }

        $image = null;
        if ($request->hasFile('image')) {
            $image = $this->upload($request);
        }

        $response = $this->sendFirebaseNotification(
            body: $request->body ?? "Speediz Notification",
            topic: "speediz",
            title: $request->title ?? "Speediz",
            image: $image,
        );

        return response()->json($response);
    }

    public function sendToDevice(Request $request)
    {
        if (empty($request->all())) {
            return response()->json([
                "success" => false,
                "message" => "No data provided",
            ]);
        }

        if (empty($request->device_token)) {
            return response()->json([
                "success" => false,
                "message" => "Device token is required",
            ]);
        }

        $image = null;
        if ($request->hasFile('image')) {
            $image = $this->upload($request);
        }

        $response = $this->sendFirebaseNotification(
            body: $request->body ?? "Speediz Notification",
            title: $request->title ?? "Speediz",
            image: $image,
            deviceToken: $request->device_token,
        );
        return $response;
    }
}
