<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\DeliveryFee;
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
            return $this->failed(null, 'Unauthorized', 'Unauthorized');
        }
        //get admin data
        $admin = Admin::where('id', $user->id)->first();
        if (!$admin) {
            return $this->failed(null, 'Unauthorized', 'Unauthorized');
        }

        $admin['email'] = $user->email;
        $currecy = Currency::query()->first();
        $admin['exchange_rate'] = (int) $currecy->exchange_rate ?? 4100;

        $delivery_fee = DeliveryFee::query()->first();
        $admin['delivery_fee'] = $delivery_fee->fee ?? 0;

        return $this->success($admin, 'Settings retrieved successfully');
    }

    //update
    public function update(Request $request)
    {
        ///update
        $user = auth()->user();
        if (!$user) {
            return $this->failed(null, 'Unauthorized', 'Unauthorized');
        }
        //get admin data
        $admin = Admin::where('id', $user->id)->first();
        if (!$admin) {
            return $this->failed(null, 'Unauthorized', 'Unauthorized');
        }

        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'username' => 'required',
            'image' => 'nullable',
            'exchange_rate' => 'nullable|numeric',
            'delivery_fee' => 'nullable|numeric',
        ]);

        //upload image
        $image = $request->image ? $request->image : null;
        if ($request->hasFile('image')) {
            $image = $this->updateImage($request, $admin);
            $data['image'] = $image;
            $admin->update($data);
        }



        //update currency
        $currency = Currency::query()->first();
        if ($currency) {
            $currency->update([
                'exchange_rate' => $request->exchange_rate ?? $currency->exchange_rate,
            ]);
        } else {
            Currency::create([
                'exchange_rate' => $request->exchange_rate ?? 4100,
            ]);
        }

        //update delivery fee
        $delivery_fee = DeliveryFee::query()->first();
        if ($delivery_fee) {
            $delivery_fee->update([
                'fee' => $request->delivery_fee ?? $delivery_fee->fee,
            ]);
        } else {
            DeliveryFee::create([
                'fee' => $request->delivery_fee ?? 0,
            ]);
        }

        return $this->success($admin, 'Settings updated successfully');
    }
}
