<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockRequest;
use App\Models\MenuItem;
use App\Services\StockService;
use App\Support\ApiResponse;

use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(
        private StockService $stockService
    ) {
    }

    public function checkAvailability(
        StockRequest $request,
        MenuItem $menuItem
    ) {
        $quantity = $request->validated()['quantity'] ?? 1;

        $availability = $this->stockService->checkAvailability(
            $menuItem,
            $quantity
        );

        $status = $availability['status'];

        unset($availability['status']);

        $message = match ($status) {
            'empty_recipe' => 'لا يمكن التحقق من المخزون لأن وصفة الصنف فارغة',
            'available' => 'المخزون كافٍ لتحضير الكمية المطلوبة',
            'insufficient_stock' => 'المخزون غير كافٍ لتحضير الكمية المطلوبة',
        };

        return ApiResponse::success(
            ['availability' => $availability],
            $message
        );
    }
}
