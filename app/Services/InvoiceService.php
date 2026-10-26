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
    public const ALLOWED_PAYMENT_METHODS_BY_CHANNEL = [
        'dine_in' => ['cash', 'card'],
        'delivery' => ['cod', 'card'],
    ];

    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepository
    ) {}

    public function createInvoice(array $data, Staff $staff): Invoice
    {
        return DB::transaction(function () use ($data, $staff) {
            $order = Order::with('orderItems.menuItem')
                ->where('restaurant_id', $staff->restaurant_id)
                ->lockForUpdate()
                ->find($data['order_id']);

            if (! $order) {
                throw ValidationException::withMessages([
                    'order_id' => ['الطلب المطلوب غير موجود أو لا يتبع هذا المطعم.'],
                ]);
            }

            $this->validatePaymentMethodForChannel($order->channel, $data['payment_method']);

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

    public function getInvoice(int $invoice, Staff $staff): ?Invoice
    {
        return $this->invoiceRepository->findByIdForRestaurant($invoice, $staff->restaurant_id);
    }

    public function payInvoice(int $invoice, float $amount, Staff $staff): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount, $staff) {
            $existingInvoice = $this->invoiceRepository->findByIdForRestaurant(
                $invoice,
                $staff->restaurant_id
            );

            if (! $existingInvoice) {
                throw ValidationException::withMessages([
                    'invoice' => ['الفاتورة غير موجودة أو لا تتبع هذا المطعم.'],
                ]);
            }

            $order = Order::where('id', $existingInvoice->order_id)
                ->where('restaurant_id', $staff->restaurant_id)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                throw ValidationException::withMessages([
                    'invoice' => ['الطلب المرتبط بهذه الفاتورة غير موجود أو لا يتبع هذا المطعم.'],
                ]);
            }

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

    private function validatePaymentMethodForChannel(string $channel, string $paymentMethod): void
    {
        $allowed = self::ALLOWED_PAYMENT_METHODS_BY_CHANNEL[$channel] ?? [];

        if (! in_array($paymentMethod, $allowed, true)) {
            throw ValidationException::withMessages([
                'payment_method' => [
                    "طريقة الدفع ({$paymentMethod}) غير صالحة لطلب من نوع ({$channel}). الطرق المتاحة: ".implode('، ', $allowed).'.',
                ],
            ]);
        }
    }
}
