<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IngredientService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private StockAvailabilityService $stockAvailabilityService
    ){
    }

    public function index(): Collection
    {
        return Ingredient::query()->get();
    }

    public function store(Staff $owner, array $data): Ingredient
    {
        $data['restaurant_id'] = $owner->restaurant_id;

        return Ingredient::create($data);
    }

    public function show(Ingredient $ingredient): Ingredient
    {
        return $ingredient;
    }

    public function update(Ingredient $ingredient, array $data): Ingredient
    {
        return DB::transaction(function () use ($ingredient, $data) {
            $ingredient->update($data);

            if (array_key_exists("quantity", $data)) {
                $this->stockAvailabilityService->refreshAffectedMenuItemsAvailability(
                    (int) $ingredient->restaurant_id,
                    [(int) $ingredient->id]
                );
            }

            return $ingredient->refresh();
        });
    }

    public function destroy(Ingredient $ingredient): void
    {
        // Prevent deletion if the ingredient has historical usage records.
        if ($ingredient->ingredientUsages()->exists()) {
            throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException(
                'لا يمكن حذف المكون لأنه مستخدم في طلبات سابقة'
            );
        }

        // Prevent deletion if the ingredient belongs to any menu item's recipe.        
        $isUsedInRecipe = MenuItem::whereHas("ingredients", function ($query) use ($ingredient) {
            $query->where("ingredients.id", $ingredient->id);
        })->exists();


        if ($isUsedInRecipe) {
            throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException(
                'لا يمكن حذف المكون لأنه مستخدم في وصفة أحد الأصناف'
            );
        }

        $ingredient->delete();
    }

}