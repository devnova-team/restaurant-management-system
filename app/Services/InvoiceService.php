<?php

namespace App\Services;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepository
    ) {}

    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $order = Order::with('orderItems.menuItem')
                ->find($data['order_id']);

            if (! $order) {
                throw ValidationException::withMessages([
                    'order_id' => ['The requested order was not found.'],
                ]);
            }

            if ($this->invoiceRepository->findByOrderId($order->id)) {
                throw ValidationException::withMessages([
                    'order_id' => ['Invoice already exists for this order.'],
                ]);
            }

            $totalAmount = $this->calculateOrderTotal($order);

            if ($totalAmount <= 0) {
                throw ValidationException::withMessages([
                    'order_id' => ['Cannot create an invoice with a zero total.'],
                ]);
            }

            return $this->invoiceRepository->create([
                'order_id' => $order->id,
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
            ]);
        });
    }

    public function getInvoice(int $invoice): ?Invoice
    {
        return $this->invoiceRepository->findById($invoice);
    }

    public function payInvoice(int $invoice, float $amount): Invoice
    {
        $invoice = $this->invoiceRepository->findById($invoice);

        if (! $invoice) {
            throw ValidationException::withMessages([
                'invoice' => ['Invoice not found.'],
            ]);
        }

        if ($invoice->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'payment_status' => ['Invoice has already been paid.'],
            ]);
        }

        if (round((float) $amount, 2) !== round((float) $invoice->total_amount, 2)) {
            throw ValidationException::withMessages([
                'amount' => ['Payment amount must be equal to the invoice total amount.'],
            ]);
        }

        return $this->invoiceRepository->markAsPaid($invoice);
    }

    private function calculateOrderTotal(Order $order): float
    {
        if ($order->orderItems->isEmpty()) {
            throw ValidationException::withMessages([
                'order_id' => ['The order has no items.'],
            ]);
        }

        $total = 0;

        foreach ($order->orderItems as $item) {
            if (! $item->menuItem) {
                throw ValidationException::withMessages([
                    'order_id' => [
                        "Menu item for order item {$item->id} was not found.",
                    ],
                ]);
            }

            if ($item->quantity <= 0 || $item->menuItem->price < 0) {
                throw ValidationException::withMessages([
                    'order_id' => ['Invalid order quantity or menu item price.'],
                ]);
            }

            $total += $item->quantity * $item->menuItem->price;
        }

        return round((float) $total, 2);
    }
}
