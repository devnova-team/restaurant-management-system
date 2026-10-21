<?php

namespace App\Repositories;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Models\Invoice;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function findById(int $id): ?Invoice
    {
        return Invoice::with([
            'order',
            'order.orderItems.menuItem',
        ])->find($id);
    }

    public function findByOrderId(int $orderId): ?Invoice
    {
        return Invoice::where('order_id', $orderId)->first();
    }

    public function markAsPaid(Invoice $invoice): Invoice
    {
        $invoice->update([
            'payment_status' => 'paid',
        ]);

        return $this->findById($invoice->id) ?? $invoice->fresh();
    }
}
