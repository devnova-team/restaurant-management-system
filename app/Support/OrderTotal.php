<?php

namespace App\Support;

use App\Models\Order;

class OrderTotal
{
    /**
     * الإجمالي المعروض للأوردر:
     * لو له فاتورة بنعرض مبلغها (بيتحدّث مع كل تعديل قبل الدفع، وبيتثبت بعده)، وإلا بنحسبه من أسعار المنيو.
     */
    public static function of(Order $order): string
    {
        $invoice = $order->relationLoaded('invoice') ? $order->invoice : null;

        return $invoice
            ? self::format((int) round((float) $invoice->total_amount * 100))
            : self::items($order);
    }

    /** مجموع (السعر × الكمية) من أسعار المنيو الحالية. محتاج orderItems.menuItem محمّلين. */
    public static function items(Order $order): string
    {
        $cents = $order->orderItems->sum(
            fn ($item) => (int) round((float) ($item->menuItem?->price ?? 0) * 100) * (int) $item->quantity
        );

        return self::format($cents);
    }

    private static function format(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
