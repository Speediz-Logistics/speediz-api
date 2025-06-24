<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePackageInvoiceRequest;
use App\Http\Resources\Admin\PackageInvoiceDetailResource;
use App\Http\Resources\Admin\PackageInvoiceResource;
use App\Http\Resources\Admin\VendorInvoiceDetailResource;
use App\Http\Resources\Admin\VendorInvoiceResource;
use App\Models\Package;
use App\Models\VendorInvoice;
use App\Traits\BaseApiResponse;

class InvoiceController extends Controller
{
    use BaseApiResponse;

    //get packages invoice or packages invoice data
    public function index()
    {
        //tab = packages or vendors
        $tab = request()->query('tab', 'packages');
        if ($tab === 'packages') {
            return $this->packagesInvoice();
        } elseif ($tab === 'vendors') {
            return $this->vendorInvoice();
        }

        return $this->failed(null, 'Invalid Tab', 'Invalid tab provided');
    }

    public function packagesInvoice()
    {
        $perPage = request()->query('per_page', config('pagination.per_page', 10));
        $search = request()->query('search'); // Search by customer phone
        $date = request()->query('date');

        $invoiceQuery = Package::with(['invoice', 'customer', 'location','shipment', 'vendor', 'driver', 'branch', 'package_type']);

        if ($date) {
            $invoiceQuery->whereHas('invoice', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            });
        }

        if ($search) {
            $invoiceQuery->whereHas('customer', function ($query) use ($search) {
                $query->where('phone', 'like', "%$search%");
            });
        }

        $paginate = $invoiceQuery->paginate($perPage);

        $invoices = [
            'data' => PackageInvoiceDetailResource::collection($paginate), // Use the stored paginated result
            'pagination' => [
                'total' => $paginate->total(),
                'per_page' => $paginate->perPage(),
                'current_page' => $paginate->currentPage(),
                'last_page' => $paginate->lastPage(),
                'from' => $paginate->firstItem(),
                'to' => $paginate->lastItem()
            ]
        ];

        return $this->success($invoices, 'Packages Invoice', 'Packages invoice data fetched successfully');
    }


    //show packages invoice
    public function showPackagesInvoice($id)
    {
        // Get invoice data from packages relationship with invoice
        $invoice = Package::with(['invoice', 'customer', 'location','shipment', 'vendor', 'driver', 'branch', 'package_type'])->find($id);

        if (!$invoice) {
            return $this->error('Invoice not found', 404);
        }

        return $this->success(PackageInvoiceDetailResource::make($invoice), 'Packages Invoice', 'Packages invoice data fetched successfully');
    }

    public function updatePackageInvoice(UpdatePackageInvoiceRequest $request, $id)
    {
        // Retrieve the package with related data
        $invoice = Package::with(['invoice', 'customer', 'location', 'shipment', 'vendor', 'driver', 'branch', 'package_type'])->find($id);

        if (!$invoice) {
            return $this->error('Invoice not found', 404);
        }

        // Update package details
        $invoice->status = $request->input('delivery.status', $invoice->status);
        $invoice->price = $request->input('package.price', $invoice->price);

        // Update related shipment data if applicable
        if ($invoice->shipment) {
            $invoice->shipment->delivery_fee = $request->input('delivery.fee', $invoice->shipment->delivery_fee);
            $invoice->shipment->save();
        }

        // Update driver contact details if applicable
        if ($invoice->driver) {
            $invoice->driver->contact_number = $request->input('driver.contact', $invoice->driver->contact_number);
            $invoice->driver->telegram_contact = $request->input('driver.telegram', $invoice->driver->telegram_contact);
            $invoice->driver->save();
        }

        // Update the invoice note if the invoice relationship exists
        if ($invoice->invoice) {
            $invoice->invoice->note = $request->input('delivery.note', $invoice->invoice->note);
            $invoice->invoice->save();
        }

        // Save the package update
        $invoice->save();

        // Return updated data using the resource
        return $this->success(
            PackageInvoiceDetailResource::make($invoice),
            'Packages Invoice',
            'Packages invoice updated successfully'
        );
    }

    //vendorInvoice
    public function vendorInvoice()
    {
        // Get invoice data from vendor relationship with invoice
        $perPage = request()->query('per_page', config('pagination.per_page', 10));
        $search = request()->query('search'); // Search by invoice number
        $date = request()->query('date');

        // Query vendor invoices and include related invoices and vendor data
        $invoiceQuery = VendorInvoice::with(['vendor', 'invoices' => function($query) {
            $query->with(['package' => function($query) {
                $query->with(['customer', 'location', 'shipment', 'vendor', 'driver', 'branch', 'package_type']);
            }]);
        }]);

        if ($date) {
            $invoiceQuery->whereDate('created_at', $date);
        }

        if ($search) {
            $invoiceQuery->where('invoice_number', 'like', "%$search%");
        }

        // Only fetch vendor invoices that have at least one associated invoice
        $invoiceQuery->whereHas('invoices');

        $paginate = $invoiceQuery->paginate($perPage);

        $invoices = [
            'data' => VendorInvoiceDetailResource::collection($paginate),
            'pagination' => [
                'total' => $paginate->total(),
                'per_page' => $paginate->perPage(),
                'current_page' => $paginate->currentPage(),
                'last_page' => $paginate->lastPage(),
                'from' => $paginate->firstItem(),
                'to' => $paginate->lastItem()
            ]
        ];

        return $this->success($invoices, 'Vendor Invoice', 'Vendor invoice data fetched successfully');
    }

    //show vendor invoice
    public function showVendorInvoice($id)
    {
        // Get invoice data from vendor relationship with invoice
        $invoice = VendorInvoice::with(['vendor', 'invoices'])->find($id);

        //get package details by invoices package_id
        $invoice->load(['invoices.package' => function ($query) {
            $query->with(['customer', 'location', 'shipment', 'vendor', 'driver', 'branch', 'package_type']);
        }]);

        if (!$invoice) {
            return $this->error('Invoice not found', 404);
        }

        $invoice = VendorInvoiceDetailResource::make($invoice);

        return $this->success($invoice, 'Vendor Invoice', 'Vendor invoice data fetched successfully');
    }
}
