<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private StockAvailabilityService $stockAvailabilityService
    ){
    }

    public function replace(MenuItem $menuItem, array $data): MenuItem
    {
        DB::transaction(function () use ($menuItem, $data) {
            $oldIngredientIds = $menuItem->ingredients()
                ->pluck("ingredients.id")
                ->map(fn ($id) => (int) $id)
                ->all();

            $ingredients = collect($data["ingredients"])
                ->mapWithKeys(function ($ingredient) {
                    return [
                        $ingredient["ingredient_id"] => [
                            "quantity_required" => $ingredient["quantity_required"],
                        ],
                    ];
                })
                ->toArray();

            $menuItem->ingredients()->sync($ingredients);

            $ingredientIds = array_values(array_unique(array_merge(
                $oldIngredientIds,
                array_map("intval", array_keys($ingredients))
            )));

            $this->stockAvailabilityService
                ->refreshAffectedMenuItemsAvailability(
                    (int) $menuItem->restaurant_id,
                    $ingredientIds
                );
        }, 3);

        return $menuItem->refresh()->load("ingredients");
    }

}
