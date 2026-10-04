<?php

namespace App\Repositories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Collection;

class MenuRepository
{
    public function getAvailableItemsFor(Restaurant $restaurant): Collection
    {
        return $restaurant->menuItems()
            ->where('is_available', true)
            ->get();
    }
}
