<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class PackageStoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sender_id' => 'required',
            'sender_name' => 'required|string|max:255',

            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:255',

            'branch_name' => 'required|string|max:255',
            'branch_phone' => 'required|string|max:255',
            'branch_address' => 'required|string|max:255',

            'package_type_name' => 'required|string|max:255',
            'package_type_description' => 'required|string|max:255',

            'package_price' => 'required|string',
            'package_price_khr' => 'nullable',

            'delivery_fee_price' => 'nullable',

            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:255',
            'driver_telegram_contact' => 'nullable|max:255',
        ];
    }
}
