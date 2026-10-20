<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItemIngredientUsage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StockOrderRestoreService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private StockAvailabilityService $stockAvailabilityService
    ){
    }

     public function restoreForOrder(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            // Lock the order to prevent duplicate restoration.
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->where("restaurant_id", $order->restaurant_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Retrieve the original usage records for this order.
            $usagesRecords = OrderItemIngredientUsage::query()
                ->whereHas("orderItem", function ($query) use ($lockedOrder) {
                    $query->where("order_id", $lockedOrder->id);
                })
                ->orderBy("id")
                ->lockForUpdate()
                ->get();

            // No old usage records means nothing to restore.
            if ($usagesRecords->isEmpty()) {
                return [
                    "order_id" => $lockedOrder->id,
                    "usage_records_restored" => 0,
                    "ingredients_restored" => 0,
                ];
            }

            // Aggregate the exact quantities previously deducted.
            $requiredByIngredient = [];

            foreach ($usagesRecords as $usageRecord) {
                $quantityUsed = round(
                    (float) $usageRecord->quantity_used,
                    2
                );

                if ($quantityUsed <= 0) {
                    throw new UnprocessableEntityHttpException(
                        "سجل استخدام يحتوي على كمية غير صالحة"
                    );
                }

                $ingredientId = (int) $usageRecord->ingredient_id;

                $requiredByIngredient[$ingredientId] = round(
                    ($requiredByIngredient[$ingredientId] ?? 0)
                        + $quantityUsed,
                    2
                );
            }

            // Lock all affected ingredients in a consistent order.
            $ingredients = Ingredient::query()
                ->whereIn("id", array_keys($requiredByIngredient))
                ->where("restaurant_id", $lockedOrder->restaurant_id)
                ->orderBy("id")
                ->lockForUpdate()
                ->get()
                ->keyBy("id");

            if (
                $ingredients->count()
                !== count($requiredByIngredient)
            ) {
                throw new UnprocessableEntityHttpException(
                    "أحد مكونات الطلب غير موجود"
                );
            }

            // Restore the original quantities before deleting usage records.
            foreach ($requiredByIngredient as $ingredientId => $quantity) {
                $restoredRows = Ingredient::query()
                    ->whereKey($ingredientId)
                    ->where("restaurant_id", $lockedOrder->restaurant_id)
                    ->increment("quantity", $quantity);

                if ($restoredRows !== 1) {
                    throw new ConflictHttpException(
                        "تعذر استعادة مخزون أحد المكونات"
                    );
                }
            }

            // Delete old usage records only after restoring the stock.
            $usageRecordIds = $usagesRecords->modelKeys();

            OrderItemIngredientUsage::query()
                ->whereIn("id", $usageRecordIds)
                ->delete();

            // Refresh availability using the existing shared service.
            $ingredientIds = array_map(
                "intval",
                array_keys($requiredByIngredient)
            );

            $this->stockAvailabilityService
                ->refreshAffectedMenuItemsAvailability(
                    (int) $lockedOrder->restaurant_id,
                    $ingredientIds
                );

            return [
                "order_id" => $lockedOrder->id,
                "usage_records_restored" => count($usageRecordIds),
                "ingredients_restored" => count($requiredByIngredient),
            ];
        }, 3);
    }
}
