<?php

namespace App\Services;

use App\Models\Ingredient;

class InventoryService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

     public function checkStockLevel(Ingredient $ingredient): array
    {
        $quantity = (float) $ingredient->quantity;
        $threshold = (float) $ingredient->low_stock_threshold;

        $isLowStock = $quantity <= $threshold;

        return [
            'ingredient_id' => $ingredient->id,
            'quantity' => $quantity,
            'low_stock_threshold' => $threshold,
            'is_low_stock' => $isLowStock,
        ];
    }
}
