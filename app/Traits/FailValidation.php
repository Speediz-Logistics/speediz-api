<?php

namespace App\Traits;

use App\Http\Requests\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait FailValidation
{
    public function failedValidation(Validator|\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'data' => null,
            'meta' => [
                'status' => false,
                'alert' => [
                    'title' => 'Validation Error',
                    'message' => 'Validation failed. Please check your input.',
                    'errors' => $validator->errors(), // Include validation errors
                ],
            ],
        ], 400));
    }
}
