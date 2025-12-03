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
        $allStatuses = ['pending', 'in_transit'];

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

    //history
    public function history(Request $request)
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
        $allStatuses = ['completed', 'cancelled'];

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

        return $this->success(new InvoiceCollection($invoices), 'List of vendor invoices history.');
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
        $dateFilter = $this->parseDateFilter(request()->query('date'));
        if ($dateFilter instanceof \Illuminate\Http\JsonResponse) {
            return $dateFilter; // return error if invalid
        }

        $vendorId = $user->vendor->id;

        $vendorInvoices = VendorInvoice::query()
            ->where('vendor_id', $vendorId)
            ->whereHas('invoices.package', function ($query) {
                $query->whereNotIn('status', ['completed', 'cancelled']);
            })
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

        $packageSummary = $this->calculatePackageSummary($vendorInvoices);

        return $this->success([
            'vendor_invoices' => $vendorInvoices,
            'package_summary' => $packageSummary,
        ], 'List of current vendor invoices.');
    }

    /**
     * Show history (completed or cancelled invoices)
     */
    public function vendorInvoiceHistory()
    {
        $user = auth()->user();
        if (!$user || !$user->vendor) {
            return $this->failed('Unauthorized access.', 403);
        }

        $perPage = request()->query('per_page', config('pagination.per_page', 10));
        $dateFilter = $this->parseDateFilter(request()->query('date'));
        if ($dateFilter instanceof \Illuminate\Http\JsonResponse) {
            return $dateFilter;
        }

        $vendorId = $user->vendor->id;

        // Vendor invoices
        $vendorInvoices = VendorInvoice::query()
            ->where('vendor_id', $vendorId)
            ->whereHas('invoices.package', function ($query) {
                $query->whereIn('status', ['completed', 'cancelled']);
            })
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

        $packageSummary = $this->calculatePackageSummary($vendorInvoices);

        return $this->success([
            'vendor_invoices' => $vendorInvoices,
            'package_summary' => $packageSummary,
        ], 'List of vendor invoice history.');
    }

    /**
     * Helper: Parse and validate date filter
     */
    protected function parseDateFilter($date)
    {
        if (!$date) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return $this->failed('Invalid date format.', 422);
        }
    }

    /**
     * Helper: Calculate package summary
     */
    protected function calculatePackageSummary($vendorInvoices)
    {
        $summary = [
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
            'in_transit' => 0,
            'cancelled' => 0,
            'payment_status' => 'unpaid',
        ];

        foreach ($vendorInvoices as $vendorInvoice) {
            foreach ($vendorInvoice->invoices as $invoice) {
                if ($invoice->package) {
                    $summary['total']++;
                    $status = $invoice->package->status;
                    if (isset($summary[$status])) {
                        $summary[$status]++;
                    }
                }
            }
        }

        return $summary;
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

        // NEW: collect all packages into a separate array
        $packages = [];

        if (!$vendorInvoice->invoices || $vendorInvoice->invoices->isEmpty()) {
            $vendorInvoice->package_summary = $packageCounts;
            $vendorInvoice->packages = [];
            return $this->success($vendorInvoice, 'Vendor invoice details with no packages.');
        }

        // Go through invoices and collect packages
        foreach ($vendorInvoice->invoices as $invoice) {
            if ($invoice->package) {
                $packages[] = $invoice->package; // extract package ONLY

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
        $vendorInvoice->packages = $packages;

        $vendorInvoice->invoices->each(function ($invoice) {
            unset($invoice->package);
        });

        return $this->success($vendorInvoice, 'Vendor invoice details.');
    }
}
