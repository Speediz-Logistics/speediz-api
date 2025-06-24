<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorInvoiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Collect packages from each invoice. This assumes that each invoice
        // has a 'package' property as shown in your JSON.
        $packages = $this->invoices->pluck('package')->filter();

        return [
            'id' => $this->id ?? null,
            'invoice_number' => $this->invoice_number ?? null,
            'date' => $this->created_at->format('Y-m-d') ?? null,
            'vendor_name' => $this->vendor?->first_name . ' ' . $this->vendor?->last_name ?? null,
            'description' => $this->description ?? null,
            'amount' => count($this->invoices) ?? 0,
            'total_invoice' => $this->total ?? 0,
            'payment_status' => $this->status ?? null,

            'to'             => optional($this->vendor)->first_name . ' ' . optional($this->vendor)->last_name,
            'phone'          => optional($this->vendor)->contact_number,
            'invoice_status' => $this->status,
            'package_status' => [
                'cancelled' => $packages->where('status', 'cancelled')->count(),
                'completed' => $packages->where('status', 'delivered')->count(),
                'pending'   => $packages->where('status', 'pending')->count(),
                'total'     => $packages->count(),
            ],
            'invoices'    => InvoiceDetailResource::collection($this->invoices),
            'grand_total' => $this->invoices->sum(function ($invoice) {
                // Cast the delivery_fee (from package->shipment) to integer.
                return (int) optional($invoice->package->shipment)->delivery_fee;
            }),
        ];
    }
}

