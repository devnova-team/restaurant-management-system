<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuItemResource;
use App\Models\Restaurant;
use App\Services\PublicMenuService;
use Illuminate\Http\JsonResponse;
use App\Support\ApiResponse;

class PublicMenuController extends Controller
{
    public function __construct(private readonly PublicMenuService $service) {}

    public function index(Restaurant $restaurant): JsonResponse
    {
        $menu = $this->service->getAvailableMenu($restaurant);


        return ApiResponse::success(
            MenuItemResource::collection($menu),
            'تم جلب المنيو بنجاح'
        );
    }
}
