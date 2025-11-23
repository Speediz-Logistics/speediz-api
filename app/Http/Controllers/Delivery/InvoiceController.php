<?php

namespace App\Http\Controllers\Delivery;

use App\Constants\ConstPackageStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Delivery\InvoiceResource;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Package;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InvoiceController extends Controller
{
    use BaseApiResponse;

    //invoices
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->failed('Unauthorized', 401);
        }

        //get driver id
        $driver = Driver::query()->where('user_id', $user->id)->first();

        // Pagination size (default 10)
        $perPage = $request->get('per_page', 10);

        $invoices = Invoice::query()
            ->where('driver_id', $driver->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->success($invoices, 'Invoices retrieved successfully');
    }

    //showInvoice
    public function showInvoice(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return $this->failed('Unauthorized', 401);
        }

        $driver = Driver::query()->where('user_id', $user->id)->first();

        if (!$driver) {
            return $this->failed('Driver not found', 404);
        }

        // Fetch single invoice with packages
        $invoice = Invoice::query()
            ->with(['packages.shipment', 'driver', 'vendor']) // eager load for better performance
            ->where('id', $id)
            ->where('driver_id', $driver->id)
            ->first();

        if (!$invoice) {
            return $this->failed('Invoice not found', 404);
        }

        // All possible statuses
        $allStatuses = [
            ConstPackageStatus::CANCELLED,
            ConstPackageStatus::PENDING,
            ConstPackageStatus::IN_TRANSIT,
            ConstPackageStatus::COMPLETED
        ];

        // Ensure packages is a collection
        $packages = $invoice->packages ?? collect();

        // Add computed fields
        $invoice->total_package_price = $packages->sum('price');
        $invoice->delivery_fee = $packages->sum(
            fn ($package) => optional($package->shipment)->delivery_fee ?? 0
        );

        // Get package status counts for this invoice
        $statusCounts = Package::query()
            ->selectRaw('status, count(*) as count')
            ->where('driver_id', $driver->id)
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses appear
        $invoice->package_status_counts = collect($allStatuses)
            ->mapWithKeys(fn ($status) => [
                $status => $statusCounts[$status] ?? 0
            ]);

        return $this->success($packages, 'Invoice retrieved successfully');
    }

}
