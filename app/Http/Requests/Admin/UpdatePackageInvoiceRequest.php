<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageInvoiceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'exists:packages,id'],

            // Personal Information
            'personal.sender_name' => ['nullable', 'string'],
            'personal.sender_phone_number' => ['nullable', 'string'],
            'personal.receiver_phone' => ['nullable', 'string'],

            // Location Details
            'location.branch' => ['nullable', 'string'],
            'location.destination' => ['nullable', 'string'],

            // Package Information
            'package.type' => ['nullable', 'string'],
            'package.name' => ['nullable', 'string'],
            'package.price' => ['nullable', 'numeric'],
            'package.price_riel' => ['nullable', 'numeric'],

            // Driver & Delivery Information
            'driver.contact' => ['nullable', 'string'],
            'driver.telegram' => ['nullable', 'string'],
            'delivery.fee' => ['nullable', 'numeric'],
            'delivery.status' => ['nullable', 'string'],
            'delivery.note' => ['nullable', 'string'],
        ];
    }

    /**
     * Transform the request into an array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->input('id'),

            'personal' => [
                'sender_name' => $this->input('personal.sender_name'),
                'sender_phone_number' => $this->input('personal.sender_phone_number'),
                'receiver_phone' => $this->input('personal.receiver_phone'),
            ],

            'location' => [
                'branch' => $this->input('location.branch'),
                'destination' => $this->input('location.destination'),
            ],

            'package' => [
                'type' => $this->input('package.type'),
                'name' => $this->input('package.name'),
                'price' => $this->input('package.price', 0),
                'price_riel' => $this->input('package.price', 0) * 4100,
            ],

            'driver' => [
                'contact' => $this->input('driver.contact'),
                'telegram' => $this->input('driver.telegram'),
            ],

            'delivery' => [
                'fee' => $this->input('delivery.fee', 0),
                'status' => $this->input('delivery.status'),
                'note' => $this->input('delivery.note'),
            ],
        ];
    }
}

