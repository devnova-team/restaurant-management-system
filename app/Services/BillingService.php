<?php

namespace App\Services;

use App\Interfaces\InvoiceInterface;
use App\Models\Invoice;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(
        protected InvoiceInterface $invoiceRepository
    ) {}

    public function calculateTotal(Order $order): float
    {
        $order->loadMissing('items.menuItem');

        return (float) $order->items->sum(function ($item) {
            return $item->menuItem->price * $item->quantity;
        });
    }

    public function createInvoice(
        Order $order,
        ?string $paymentMethod = null
    ): Invoice {

        $existingInvoice = $this->invoiceRepository
            ->findByOrderId($order->id);

        if ($existingInvoice) {
            throw new Exception(
                'Invoice already exists for this order.'
            );
        }

        $total = $this->calculateTotal($order);

        return DB::transaction(function () use (
            $order,
            $total,
            $paymentMethod
        ) {
            return $this->invoiceRepository->create([
                'order_id' => $order->id,
                'total_amount' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
            ]);
        });
    }

    public function updatePaymentStatus(
        Invoice $invoice,
        string $status
    ): Invoice {
        return $this->invoiceRepository->update(
            $invoice,
            [
                'payment_status' => $status,
            ]
        );
    }
}
