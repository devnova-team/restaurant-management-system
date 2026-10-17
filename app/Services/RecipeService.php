<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function () use ($menuItem, $data) {
            $ingredients = collect($data['ingredients'])
                ->mapWithKeys(function ($ingredient) {
                    return [
                        $ingredient['ingredient_id'] => [
                            'quantity_required' => $ingredient['quantity_required'],
                        ],
                    ];
                })
                ->toArray();

            $menuItem->ingredients()->sync($ingredients);
        });

        return $menuItem->load('ingredients');
    }

}
