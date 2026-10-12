<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class StockDeductionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function deduct(
        MenuItem $menuItem,
        int $requestedQuantity = 1
    ): array {
        if ($requestedQuantity < 1) {
            throw new InvalidArgumentException(
                'الكمية المطلوبة يجب أن تكون أكبر من صفر'
            );
        }

        return DB::transaction(function () use (
            $menuItem,
            $requestedQuantity
        ) {
            $menuItem = MenuItem::query()
                ->whereKey($menuItem->id)
                ->where('restaurant_id', $menuItem->restaurant_id)
                ->firstOrFail();

            $ingredients = $menuItem->ingredients()->get();

            if ($ingredients->isEmpty()) {
                throw new InvalidArgumentException(
                    'لا يمكن خصم المخزون لأن وصفة الصنف فارغة'
                );
            }

            foreach ($ingredients as $ingredient) {
                $quantityRequired =
                    (float) $ingredient->pivot->quantity_required
                    * $requestedQuantity;

                if ($quantityRequired <= 0) {
                    throw new InvalidArgumentException(
                        'كمية المكوّن المطلوبة يجب أن تكون أكبر من صفر'
                    );
                }

                $deductedRows = Ingredient::query()
                    ->whereKey($ingredient->id)
                    ->where('restaurant_id', $menuItem->restaurant_id)
                    ->where('quantity', '>=', $quantityRequired)
                    ->decrement('quantity', $quantityRequired);

                if ($deductedRows !== 1) {
                    throw new ConflictHttpException(
                        'المخزون غير كافٍ لأحد مكونات الصنف'
                    );
                }
            }

            return [
                'menu_item_id' => $menuItem->id,
                'deducted_quantity' => $requestedQuantity,
            ];
        });
    }
}
