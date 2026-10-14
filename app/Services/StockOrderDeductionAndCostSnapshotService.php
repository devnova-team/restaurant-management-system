<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemIngredientUsage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StockOrderDeductionAndCostSnapshotService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function deductForOrder(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            // Lock the order to serialize concurrent deduction attempts.
            // This service does not manage the order's status.
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->where('restaurant_id', $order->restaurant_id)
                ->lockForUpdate()
                ->firstOrFail();

            $orderItems = OrderItem::query()
                ->where('order_id', $lockedOrder->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->with('menuItem.ingredients')
                ->get();

            if ($orderItems->isEmpty()) {
                throw new UnprocessableEntityHttpException(
                    'لا يمكن خصم المخزون لأن الطلب لا يحتوي على أصناف'
                );
            }

            // Reject a repeated deduction if usage records already exist.
            $orderItemIds = $orderItems->modelKeys();

            if (
                OrderItemIngredientUsage::query()
                    ->whereIn('order_item_id', $orderItemIds)
                    ->exists()
            ) {
                throw new ConflictHttpException(
                    'تم خصم المخزون وتسجيل استخدام المكونات لهذا الطلب من قبل'
                );
            }

            $usageRows = [];
            $requiredByIngredient = [];

            foreach ($orderItems as $orderItem) {
                $menuItem = $orderItem->menuItem;

                if (
                    !$menuItem ||
                    (int) $menuItem->restaurant_id
                        !== (int) $lockedOrder->restaurant_id
                ) {
                    throw new UnprocessableEntityHttpException(
                        'أحد أصناف الطلب غير صالح لهذا المطعم'
                    );
                }

                $quantity = (int) $orderItem->quantity;

                if ($quantity < 1) {
                    throw new UnprocessableEntityHttpException(
                        'كمية أحد أصناف الطلب غير صالحة'
                    );
                }

                $ingredients = $menuItem->ingredients;

                if ($ingredients->isEmpty()) {
                    throw new UnprocessableEntityHttpException(
                        "وصفة الصنف ({$menuItem->name}) فارغة"
                    );
                }

                foreach ($ingredients as $ingredient) {
                    $quantityPerItem = round(
                        (float) $ingredient->pivot->quantity_required,
                        2
                    );

                    if ($quantityPerItem <= 0) {
                        throw new UnprocessableEntityHttpException(
                            'كمية أحد مكونات الوصفة يجب أن تكون أكبر من صفر'
                        );
                    }

                    $quantityUsed = round(
                        $quantityPerItem * $quantity,
                        2
                    );

                    $ingredientId = (int) $ingredient->id;

                    $requiredByIngredient[$ingredientId] = round(
                        ($requiredByIngredient[$ingredientId] ?? 0)
                            + $quantityUsed,
                        2
                    );

                    $usageRows[] = [
                        'order_item_id' => $orderItem->id,
                        'ingredient_id' => $ingredientId,
                        'quantity_used' => $quantityUsed,
                    ];
                }
            }

            // Lock all required ingredients in a consistent order.
            $ingredients = Ingredient::query()
                ->whereIn('id', array_keys($requiredByIngredient))
                ->where('restaurant_id', $lockedOrder->restaurant_id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if (
                $ingredients->count()
                !== count($requiredByIngredient)
            ) {
                throw new UnprocessableEntityHttpException(
                    'أحد مكونات الوصفات غير موجود أو لا يتبع المطعم'
                );
            }

            // Validate every ingredient before deducting any stock.
            foreach ($requiredByIngredient as $ingredientId => $required) {
                $ingredient = $ingredients->get($ingredientId);

                if ((float) $ingredient->quantity < $required) {
                    throw new ConflictHttpException(
                        "المخزون غير كافٍ للمكوّن: {$ingredient->name}"
                    );
                }

                if ((float) $ingredient->unit_price < 0) {
                    throw new UnprocessableEntityHttpException(
                        "سعر المكوّن غير صالح: {$ingredient->name}"
                    );
                }
            }

            // Deduct the aggregated quantity once per ingredient.
            foreach ($requiredByIngredient as $ingredientId => $required) {
                $deductedRows = Ingredient::query()
                    ->whereKey($ingredientId)
                    ->where('restaurant_id', $lockedOrder->restaurant_id)
                    ->where('quantity', '>=', $required)
                    ->decrement('quantity', $required);

                if ($deductedRows !== 1) {
                    throw new ConflictHttpException(
                        'تعذر خصم المخزون بسبب تغير الكميات المتاحة'
                    );
                }
            }

            // Record the usage and snapshot cost in the same transaction.
            $totalCost = 0.0;

            foreach ($usageRows as $usage) {
                $ingredient = $ingredients->get(
                    $usage['ingredient_id']
                );

                $cost = round(
                    $usage['quantity_used']
                        * (float) $ingredient->unit_price,
                    2
                );

                OrderItemIngredientUsage::create([
                    'order_item_id' => $usage['order_item_id'],
                    'ingredient_id' => $usage['ingredient_id'],
                    'quantity_used' => $usage['quantity_used'],
                    'cost_at_time_of_use' => $cost,
                ]);

                $totalCost = round($totalCost + $cost, 2);
            }

            return [
                'order_id' => $lockedOrder->id,
                'items_processed' => $orderItems->count(),
                'usage_records_created' => count($usageRows),
                'total_cost' => $totalCost,
            ];
        }, 3);
    }
}
