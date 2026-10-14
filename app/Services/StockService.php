<?php

namespace App\Services;

use App\Models\MenuItem;
use InvalidArgumentException;

class StockService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private StockAvailabilityService $stockAvailabilityService
    ) {
    }

    public function checkAvailability(
        MenuItem $menuItem,
        int $requestedQuantity = 1
    ): array {
        if ($requestedQuantity < 1) {
            throw new InvalidArgumentException(
                'الكمية المطلوبة يجب أن تكون أكبر من صفر'
            );
        }

        $menuItem->loadMissing('ingredients');

        $ingredients = $menuItem->ingredients;

        if ($ingredients->isEmpty()) {
            return [
                'menu_item_id' => $menuItem->id,
                'requested_quantity' => $requestedQuantity,
                'status' => 'empty_recipe',
                'is_available' => false,
                'max_quantity' => 0,
                'ingredients' => [],
            ];
        }

        $availability = $this->stockAvailabilityService->check(
            $ingredients,
            $requestedQuantity
        );

        return [
            'menu_item_id' => $menuItem->id,
            'requested_quantity' => $requestedQuantity,
            'status' => $availability['is_available']
                ? 'available'
                : 'insufficient_stock',
            'is_available' => $availability['is_available'],
            'max_quantity' => $availability['max_quantity'],
            'ingredients' => $availability['ingredients'],
        ];
    }
}
