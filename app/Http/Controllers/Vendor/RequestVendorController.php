<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Requests\Vendor\RequestVendorRequest;
use App\Models\RequestVendor;
use App\Traits\BaseApiResponse;
use App\Http\Controllers\Controller;


class RequestVendorController extends Controller
{
    use BaseApiResponse;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $vendors = RequestVendor::all();
        return response()->json($vendors);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(RequestVendorRequest $request)
    {
        if ($request->fails()) {
            return $this->failed($request->errors(), 'Error', 'Error occurred');
        }

        $vendor = RequestVendor::create($request->validated());

        return $this->success($vendor, 'Vendor created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $vendor = RequestVendor::find($id);

        if (!$vendor) {
            return $this->failed('Vendor not found.');
        }

        return $this->success($vendor, 'Vendor retrieved successfully.');
    }

}
