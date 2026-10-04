<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Repositories\MenuRepository;
use Illuminate\Database\Eloquent\Collection;

class PublicMenuService
{
    public function __construct(private readonly MenuRepository $repository) {}

    public function getAvailableMenu(Restaurant $restaurant): Collection
    {
        return $this->repository->getAvailableItemsFor($restaurant);
    }
}
