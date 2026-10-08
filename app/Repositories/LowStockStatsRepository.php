<?php

namespace App\Repositories;

use App\Models\Ingredient;
use App\Repositories\Interfaces\LowStockStatsRepositoryInterface;

class LowStockStatsRepository implements LowStockStatsRepositoryInterface
{
    public function getLowStockIngredients(): array
    {
        return Ingredient::query()
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->get([
                'id',
                'name',
                'quantity',
                'unit',
                'low_stock_threshold',
            ])
            ->map(fn ($ingredient) => [
                'ingredient_id' => (int) $ingredient->id,
                'name' => $ingredient->name,
                'quantity' => (float) $ingredient->quantity,
                'unit' => $ingredient->unit,
                'low_stock_threshold' => (float) $ingredient->low_stock_threshold,
            ])
            ->toArray();
    }
}