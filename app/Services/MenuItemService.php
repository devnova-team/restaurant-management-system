<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        $data['is_available'] = false; // Set is_available to false by default when creating a new menu item
        return MenuItem::create($data);
    }

    public function update(MenuItem $menuItem, array $data): MenuItem
    {
        $menuItem->update($data);
        return $menuItem;
    }

    public function destroy(MenuItem $menuItem): void
    {
        DB::transaction(function () use ($menuItem) {
            $menuItem->ingredients()->detach();
            $menuItem->update(['is_available' => false]);
        });
    }
}
