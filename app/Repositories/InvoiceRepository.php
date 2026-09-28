<?php

namespace App\Repositories;

use App\Interfaces\InvoiceInterface;
use App\Models\Invoice;

class InvoiceRepository implements InvoiceInterface
{
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function findById(int $id): ?Invoice
    {
        return Invoice::with([
            'order.items.menuItem',
        ])->find($id);
    }

    public function findByOrderId(int $orderId): ?Invoice
    {
        return Invoice::where(
            'order_id',
            $orderId
        )->first();
    }

    public function update(
        Invoice $invoice,
        array $data
    ): Invoice {
        $invoice->update($data);

        return $invoice->fresh([
            'order.items.menuItem',
        ]);
    }
}
