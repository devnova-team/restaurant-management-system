<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreMenuItemRequest;
use App\Http\Requests\Menu\UpdateMenuItemRequest;
use App\Http\Resources\OwnerMenuItemResource;
use App\Models\MenuItem;
use App\Services\MenuItemService;
use App\Support\ApiResponse;

class MenuItemController extends Controller
{
    public function __construct(private MenuItemService $menuItemService) {}

    public function index()
    {
        $menuItems = $this->menuItemService->index();

        return ApiResponse::success([
            'menu_items' => OwnerMenuItemResource::collection($menuItems),
        ], 'تم استرجاع قائمة المنيو بنجاح.');
    }

    public function store(StoreMenuItemRequest $request)
    {
        $menuItem = $this->menuItemService->store($request->user(), $request->validated());

        return ApiResponse::success([
            'menu_item' => new OwnerMenuItemResource($menuItem),
        ], 'تم إضافة الصنف بنجاح', 201);    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem)
    {

        $menuItem = $this->menuItemService->update($menuItem, $request->validated());
        // dd($menuItem, $menuItem->name, $menuItem->exists);

        return ApiResponse::success([
            'menu_item' => new OwnerMenuItemResource($menuItem),
        ], 'تم تعديل الصنف بنجاح');
    }

    public function destroy(MenuItem $menuItem)
    {
        $this->menuItemService->destroy($menuItem);

        return ApiResponse::success(null, 'تم تعطيل الصنف بنجاح.');
    }
}
