<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? null,
            'invoice_number' => $this->invoice_number ?? null,
            'date' => $this->created_at->format('Y-m-d') ?? null,
            'vendor_name' => $this->vendor?->first_name . ' ' . $this->vendor?->last_name ?? null,
            'description' => $this->description ?? null,
            'amount' => count($this->invoices) ?? 0,
            'total_invoice' => $this->total ?? 0,
            'payment_status' => $this->status ?? null,
        ];
    }
}
