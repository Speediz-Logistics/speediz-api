<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class RequestPackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Update this to implement actual authorization logic
        return true; // Allow all requests for now
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'number' => 'required|unique:packages,number',
            'name' => 'required|string|max:255',
            'slug' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'zone' => 'nullable|string|max:255',

            'Customer_first_name' => 'nullable|string|max:255',
            'Customer_last_name' => 'nullable|string|max:255',
            'Customer_phone' => 'nullable|string|max:15',

            'location' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',

            'status' => 'nullable|string',
        ];
    }

    /**
     * Get custom validation messages for the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'number.required' => 'The package number is required.',
            'number.string' => 'The package number must be a string.',
            'number.unique' => 'The package number must be unique.',
            'name.required' => 'The package name is required.',
            'name.string' => 'The package name must be a string.',
            'name.max' => 'The package name may not be greater than 255 characters.',
            'slug.required' => 'The package slug is required.',
            'slug.string' => 'The package slug must be a string.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.',
            'description.string' => 'The description must be a string.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'The image may not be greater than 2MB.',
            'zone.string' => 'The zone must be a string.',
            'zone.max' => 'The zone may not be greater than 255 characters.',

            'Customer_first_name.string' => 'The customer first name must be a string.',
            'Customer_first_name.max' => 'The customer first name may not be greater than 255 characters.',
            'Customer_last_name.string' => 'The customer last name must be a string.',
            'Customer_last_name.max' => 'The customer last name may not be greater than 255 characters.',
            'Customer_phone.string' => 'The customer phone must be a string.',
            'Customer_phone.max' => 'The customer phone may not be greater than 15 characters.',

            'location.string' => 'The location must be a string.',
            'location.max' => 'The location may not be greater than 255 characters.',
            'lat.numeric' => 'The latitude must be a valid number.',
            'lng.numeric' => 'The longitude must be a valid number.',

            'status.string' => 'The status must be a string.',
        ];
    }
}
