<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Staff;
use Illuminate\Support\Collection;

class MenuItemService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    public function index(): Collection
    {
        return MenuItem::all();
    }

    public function store(Staff $owner, array $data)
    {
        $data['restaurant_id'] = $owner->restaurant_id;
        return MenuItem::create($data);
    }

    public function update(MenuItem $menuItem, array $data): MenuItem
    {
        $menuItem->update($data);
        return $menuItem;
    }

    public function destroy(MenuItem $menuItem): void
    {
        $menuItem->update(['is_available' => false]);
    }
}
