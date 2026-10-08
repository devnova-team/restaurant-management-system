<?php

namespace App\Services;

use App\Models\MenuItem;

class RecipeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function replace(MenuItem $menuItem, array $data): MenuItem
    {
        $menuItem->ingredients()->sync(
            collect($data['ingredients'])->mapWithKeys(function ($ingredient) {
                return [
                    $ingredient['ingredient_id'] => [
                        'quantity_required' => $ingredient['quantity_required'],
                    ],
                ];
            })->toArray()
        );

        return $menuItem->load('ingredients');
    }

}
