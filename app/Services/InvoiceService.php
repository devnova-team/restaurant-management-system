<?php

namespace App\Services;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepository
    ) {}

    public function createInvoice(array $data, ?Staff $staff = null): Invoice
    {
        return DB::transaction(function () use ($data, $staff) {
            $orderQuery = Order::with('orderItems.menuItem');

            if ($staff) {
                $orderQuery->where('restaurant_id', $staff->restaurant_id);
            }

            $order = $orderQuery->find($data['order_id']);

            if (! $order) {
                throw ValidationException::withMessages([
                    'order_id' => ['الطلب المطلوب غير موجود أو لا يتبع هذا المطعم.'],
                ]);
            }

            if ($this->invoiceRepository->findByOrderId($order->id)) {
                throw ValidationException::withMessages([
                    'order_id' => ['توجد فاتورة مسجلة بالفعل لهذا الطلب.'],
                ]);
            }

            $totalAmount = $this->calculateOrderTotal($order);

            if ($totalAmount <= 0) {
                throw ValidationException::withMessages([
                    'order_id' => ['لا يمكن إنشاء فاتورة بإجمالي صفر.'],
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

    public function getInvoice(int $invoice, ?Staff $staff = null): ?Invoice
    {
        if ($staff) {
            return $this->invoiceRepository->findByIdForRestaurant($invoice, $staff->restaurant_id);
        }

        return $this->invoiceRepository->findById($invoice);
    }

    public function payInvoice(int $invoice, float $amount, Staff $staff): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount, $staff) {
            $invoiceModel = $this->invoiceRepository->findByIdForRestaurant(
                $invoice,
                $staff->restaurant_id,
                lockForUpdate: true
            );

            if (! $invoiceModel) {
                throw ValidationException::withMessages([
                    'invoice' => ['الفاتورة غير موجودة أو لا تتبع هذا المطعم.'],
                ]);
            }

            if ($invoiceModel->payment_status === 'paid') {
                throw ValidationException::withMessages([
                    'payment_status' => ['تم سداد هذه الفاتورة مسبقاً.'],
                ]);
            }

            if (round((float) $amount, 2) !== round((float) $invoiceModel->total_amount, 2)) {
                throw ValidationException::withMessages([
                    'amount' => ['المبلغ المدفوع يجب أن يكون مساوياً لإجمالي قيمة الفاتورة.'],
                ]);
            }

            return $this->invoiceRepository->markAsPaid($invoiceModel);
        });
    }

    private function calculateOrderTotal(Order $order): float
    {
        if ($order->orderItems->isEmpty()) {
            throw ValidationException::withMessages([
                'order_id' => ['الطلب لا يحتوي على أي عناصر.'],
            ]);
        }

        $total = 0;

        foreach ($order->orderItems as $item) {
            if (! $item->menuItem) {
                throw ValidationException::withMessages([
                    'order_id' => [
                        "لم يتم العثور على عنصر القائمة الخاص بعنصر الطلب {$item->id}.",
                    ],
                ]);
            }

            if ($item->quantity <= 0 || $item->menuItem->price < 0) {
                throw ValidationException::withMessages([
                    'order_id' => ['كمية الطلب أو سعر الصنف غير صالح.'],
                ]);
            }

            $total += $item->quantity * $item->menuItem->price;
        }

        return round((float) $total, 2);
    }
}
