<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\InvoiceCollection;
use App\Http\Resources\Vendor\InvoiceResource;
use App\Models\Package;
use App\Models\VendorInvoice;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InvoiceController extends Controller
{
    use BaseApiResponse;



    public function index(Request $request)
    {
        $user = auth()->user();
        $perPage = $request->query('per_page', config('pagination.per_page', 10));
        $dateFilter = $request->query('date');

        // Reformat date to match Laravel's 'created_at' format (YYYY-MM-DD)
        if ($dateFilter) {
            try {
                $dateFilter = Carbon::parse($dateFilter)->format('Y-m-d');
            } catch (\Exception $e) {
                return $this->failed('Invalid date format.', 422);
            }
        }

        $invoices = $user->vendor->invoices()
            ->with([
                'packages.vendor',
                'packages.customer',
                'packages.location',
                'packages.shipment',
                'driver',
                'packages',
                'employee'
            ])
            ->when($request->query('search'), fn($query, $search) => $query->where('number', 'like', "%$search%"))
            ->when($dateFilter, fn($query, $date) => $query->whereDate('created_at', $date))
            ->paginate($perPage);

        // Define all possible statuses
        $allStatuses = ['completed', 'pending', 'in_transit', 'cancelled'];

        // Convert paginator collection and modify invoices
        $invoices->getCollection()->transform(function ($invoice) use ($allStatuses, $dateFilter) {
            // Ensure packages is a collection
            $packages = $invoice->packages ?? collect();

            $invoice->total_package_price = $packages->sum('price');
            $invoice->delivery_fee = $packages->sum(fn($package) => optional($package->shipment)->delivery_fee ?? 0);

            // Get the package status counts
            $statusCounts = Package::query()
                ->selectRaw('status, count(*) as count')
                ->where('vendor_id', $invoice->vendor->id)
                ->when($dateFilter, fn($query, $date) => $query->whereDate('created_at', $date))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Ensure all statuses exist with default 0
            $invoice->package_status_counts = collect($allStatuses)
                ->mapWithKeys(fn($status) => [$status => $statusCounts[$status] ?? 0]);

            return $invoice;
        });

        return $this->success(new InvoiceCollection($invoices), 'List of vendor invoices.');
    }

    //show
    public function show($id)
    {
        $invoice = auth()->user()->vendor->invoices()->with([
            'packages.vendor',
            'packages.customer',
            'packages.location',
            'packages.shipment',
            'driver',
            'packages',
            'employee'
        ])->findOrFail($id);

        return $this->success(new InvoiceResource($invoice), 'Vendor invoice details.');
    }

    //vendorInvoice
    public function vendorInvoice()
    {
        $user = auth()->user();
        if (!$user || !$user->vendor) {
            return $this->failed('Unauthorized access.', 403);
        }

        $perPage = request()->query('per_page', config('pagination.per_page', 10));
        $dateFilter = request()->query('date');
        if ($dateFilter) {
            try {
                $dateFilter = Carbon::parse($dateFilter)->format('Y-m-d');
            } catch (\Exception $e) {
                return $this->failed('Invalid date format.', 422);
            }
        }
        $vendorId = $user->vendor->id;

        // Vendor invoices
        $vendorInvoices = VendorInvoice::query()
            ->where('vendor_id', $vendorId)
            ->with([
                'vendor',
                'invoices',
                'invoices.customer',
                'invoices.driver',
                'invoices.package',
                'invoices.vendor',
            ])
            ->when($dateFilter, fn($query, $date) => $query->whereDate('created_at', $date))
            ->paginate($perPage);

        // Initialize overall package summary
        $packageSummary = [
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
            'in_transit' => 0,
            'cancelled' => 0,
        ];

        // Iterate over all vendor invoices and accumulate package counts
        foreach ($vendorInvoices as $vendorInvoice) {
            foreach ($vendorInvoice->invoice as $invoice) {
                if ($invoice->package) {
                    $packageSummary['total']++;

                    switch ($invoice->package->status) {
                        case 'completed':
                            $packageSummary['completed']++;
                            break;
                        case 'pending':
                            $packageSummary['pending']++;
                            break;
                        case 'in_transit':
                            $packageSummary['in_transit']++;
                            break;
                        case 'cancelled':
                            $packageSummary['cancelled']++;
                            break;
                    }
                }
            }
        }

        $packageSummary['payment_status'] = 'unpaid'; // Add payment status

        return $this->success([
            'vendor_invoices' => $vendorInvoices,
            'package_summary' => $packageSummary, // Attach the package summary to the response
        ], 'List of vendor invoices.');
    }

    //vendorInvoiceShow
    public function vendorInvoiceShow($id)
    {
        $user = auth()->user();

        if (!$user || !$user->vendor) {
            return $this->failed('Unauthorized access.', 403);
        }

        $vendorId = $user->vendor->id;
        $dateFilter = request()->query('date');

        $vendorInvoice = VendorInvoice::where('vendor_id', $vendorId)
            ->with([
                'vendor',
                'invoices',
                'invoices.customer',
                'invoices.driver',
                'invoices.package',
            ])
            ->when($dateFilter, function ($query, $date) {
                return $query->whereDate('created_at', $date);
            })
            ->find($id);

        if (!$vendorInvoice) {
            return $this->failed('Vendor invoice not found.', 404);
        }

        // Initialize package count
        $packageCounts = [
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
            'in_transit' => 0,
            'cancelled' => 0,
        ];

        // Count package statuses
        foreach ($vendorInvoice->invoice as $invoice) {
            if ($invoice->package) {
                $packageCounts['total']++;

                switch ($invoice->package->status) {
                    case 'completed':
                        $packageCounts['completed']++;
                        break;
                    case 'pending':
                        $packageCounts['pending']++;
                        break;
                    case 'in_transit':
                        $packageCounts['in_transit']++;
                        break;
                    case 'cancelled':
                        $packageCounts['cancelled']++;
                        break;
                }
            }
        }

        // Add counts to the response
        $vendorInvoice->package_summary = $packageCounts;

        return $this->success($vendorInvoice, 'Vendor invoice details.');
    }
}
