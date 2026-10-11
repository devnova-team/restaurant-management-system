<?php

namespace App\Services;

use Illuminate\Support\Collection;

class StockAvailabilityService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private InventoryService $inventoryService
    ) {
    }

    public function check(
        Collection $ingredients,
        int $requestedQuantity
    ): array {
        $results = [];
        $maxQuantity = PHP_INT_MAX;
        $isAvailable = true;

        foreach ($ingredients as $ingredient) {
            $required = (float) $ingredient->pivot->quantity_required;
            $available = (float) $ingredient->quantity;

            $totalRequired = $required * $requestedQuantity;
            $ingredientAvailable = $available >= $totalRequired;

            $ingredientMax = $required > 0
                ? (int) floor($available / $required)
                : 0;

            $stockLevel = $this->inventoryService
                ->checkStockLevel($ingredient);

            $maxQuantity = min($maxQuantity, $ingredientMax);

            if (!$ingredientAvailable) {
                $isAvailable = false;
            }

            $results[] = [
                'ingredient_id' => $ingredient->id,
                'ingredient_name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'quantity_available' => $available,
                'quantity_required_per_item' => $required,
                'quantity_required_total' => $totalRequired,
                'shortage' => max(
                    0,
                    round($totalRequired - $available, 2)
                ),
                'is_available' => $ingredientAvailable,
                'is_low_stock' => $stockLevel['is_low_stock'],
            ];
        }

        return [
            'is_available' => $isAvailable,
            'max_quantity' => $maxQuantity === PHP_INT_MAX
                ? 0
                : $maxQuantity,
            'ingredients' => $results,
        ];
    }
}
