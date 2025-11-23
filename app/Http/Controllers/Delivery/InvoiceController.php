<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Invoice;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

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
            ->select('id', 'driver_id', 'amount', 'status', 'created_at') // optimize select
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

        // Fetch only the driver's own invoice
        $invoice = Invoice::query()
            ->where('id', $id)
            ->where('driver_id', $user->id)
            ->first();

        if (!$invoice) {
            return $this->failed('Invoice not found', 404);
        }

        return $this->success($invoice, 'Invoice retrieved successfully');
    }

}
