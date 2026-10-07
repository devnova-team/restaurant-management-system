<?php

namespace App\Services;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepository
    ) {}

    public function createInvoice(array $data): Invoice
    {
        // Order is guaranteed to exist and unique by Form Request validation
        $order = Order::find($data['order_id']);

        $totalAmount = $this->calculateOrderTotal($order);

        return $this->invoiceRepository->create([
            'order_id' => $order->id,
            'total_amount' => $totalAmount,
            'payment_method' => $data['payment_method'],
            'payment_status' => 'unpaid',
        ]);
    }

    public function getInvoice(int $id): ?Invoice
    {
        return $this->invoiceRepository->findById($id);
    }

    public function updatePaymentStatus(
        int $id,
        string $status
    ): Invoice {
        $invoice = $this->invoiceRepository->findById($id);

        if (! $invoice) {
            throw ValidationException::withMessages([
                'invoice' => [
                    'Invoice not found.',
                ],
            ]);
        }

        return $this->invoiceRepository->updatePaymentStatus(
            $invoice,
            $status
        );
    }

    private function calculateOrderTotal(Order $order): float
    {
        $order->loadMissing('orderItems.menuItem');

        return (float) $order->orderItems->sum(function ($item) {
            return $item->quantity * ($item->menuItem->price ?? 0);
        });
    }
}
