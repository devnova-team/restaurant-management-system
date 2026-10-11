<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Staff;
use Illuminate\Support\Collection;

class IngredientService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
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
        $ingredient->update($data);

        return $ingredient;
    }

    public function destroy(Ingredient $ingredient): void
    {
        // Prevent deletion if the ingredient has historical usage records.
        if ($ingredient->ingredientUsages()->exists()) {
            throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException(
                'لا يمكن حذف المكون لأنه مستخدم في طلبات سابقة'
            );
        }

        // Prevent deletion if the ingredient belongs to an active menu item's recipe.
        $isUsedInActiveMenuItem = MenuItem::where('is_available', true)
            ->whereHas('ingredients', function ($query) use ($ingredient) {
                $query->where('ingredients.id', $ingredient->id);
            })
            ->exists();

        if ($isUsedInActiveMenuItem) {
            throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException(
                'لا يمكن حذف المكون لأنه مستخدم في وصفة صنف نشط'
            );
        }

        $ingredient->delete();
    }

}