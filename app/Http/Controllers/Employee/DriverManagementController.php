<?php

namespace App\Http\Controllers\Employee;

use App\Constants\ConstShipmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Shipment;
use App\Models\Vendor;
use App\Models\VendorInvoice;
use App\Traits\BaseApiResponse;
use Faker\Core\Number;
use Illuminate\Http\Request;

class DriverManagementController extends Controller
{
    use BaseApiResponse;
    //assignDriver
    public function assignDriver(Request $request)
    {
        //validate request
        $request->validate([
            'package_id' => 'required',
            'driver_id' => 'required',
            'shipment_number' => 'required',
            'shipment_type' => 'required',
            'shipment_description' => 'required',
            'shipment_date' => 'required',
            'shipment_delivery_fee' => 'required',
        ]);

        //check shipment_number is unique
        $shipment = Shipment::where('number', $request->shipment_number)->first();
        if ($shipment) {
            return $this->failed(null, 'Shipment number already exists', 'Shipment number already exists', 400);
        }

        //check if driver is invalid
        $driver = Driver::find($request->driver_id);
        if (!$driver) {
            return $this->failed(null, 'Driver not found', 'Driver not found', 404);
        }
        $shipment = new Shipment();
        $shipment->package_id = $request->package_id;
        $shipment->number = $request->shipment_number;
        $shipment->type = $request->shipment_type;
        $shipment->description = $request->shipment_description;
        $shipment->date = $request->shipment_date;
        $shipment->delivery_fee = $request->shipment_delivery_fee;
        $shipment->status = ConstShipmentStatus::PENDING;
        $shipment->save();

        //validate package id and check if package is invalid
        $package = Package::find($request->package_id);
        if (!$package) {
            return $this->failed(null, 'Package not found', 'Package not found', 404);
        }
        $package->driver_id = $request->driver_id;
        $package->shipment_id = $shipment->id;
        $package->save();

        //return response
        return $this->success($package, 'Driver assigned successfully');
    }

    //create Vendor Invoice
    public function createVendorInvoice(Request $request)
    {
        // Validate request
        $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'integer|exists:invoices,id',
            'vendor_id' => 'required|integer|exists:vendors,id',
            'description' => 'required|string',
        ]);

        // Fetch vendor
        $vendor = Vendor::find($request->vendor_id);
        if (!$vendor) {
            return $this->failed(null, 'Vendor not found', 'Vendor not found', 404);
        }

        // Fetch invoices
        $invoiceIds = $request->invoice_ids;
        $invoices = Invoice::whereIn('id', $invoiceIds)->get();
        if ($invoices->count() != count($invoiceIds)) {
            return $this->failed(null, 'One or more invoices not found', 'Invoice not found', 404);
        }

        // Check if invoices are already assigned to a vendor invoice
        $assignedInvoices = Invoice::whereIn('id', $invoiceIds)->whereNotNull('vendor_invoice_id')->pluck('id')->toArray();
        if (!empty($assignedInvoices)) {
            return $this->failed(['already_assigned_invoice_ids' => $assignedInvoices],
                'One or more invoices are already assigned to a vendor invoice', 'Invoice already assigned', 400);
        }

        // Generate random invoice number
        $randomNumber = 'INV-' . rand(1000, 9999);

        // Create vendor invoice
        $vendorInvoice = new VendorInvoice();
        $vendorInvoice->vendor_id = $request->vendor_id;
        $vendorInvoice->invoice_number = $randomNumber;
        $vendorInvoice->total = $invoices->sum('total');
        $vendorInvoice->description = $request->description;
        $vendorInvoice->status = 'unpaid';
        $vendorInvoice->save();

        // Update invoices with vendor invoice ID
        Invoice::whereIn('id', $invoiceIds)->update(['vendor_invoice_id' => $vendorInvoice->id]);

        // Return response
        return $this->success($vendorInvoice, 'Vendor invoice created successfully');
    }

}
