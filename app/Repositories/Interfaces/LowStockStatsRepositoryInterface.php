<?php

namespace App\Repositories\Interfaces;

interface LowStockStatsRepositoryInterface
{
    public function getLowStockIngredients(): array;
}