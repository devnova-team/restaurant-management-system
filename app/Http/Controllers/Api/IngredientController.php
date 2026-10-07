<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\IngredientResource;
use App\Services\IngredientService;
use App\Support\ApiResponse;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use App\Models\Ingredient;

class IngredientController extends Controller
{
    public function __construct(private IngredientService $ingredientService)
    {
    }

    public function index()
    {
        $ingredients = $this->ingredientService->index();

        return ApiResponse::success([
            'ingredients' => IngredientResource::collection($ingredients),
        ], 'تم جلب بيانات المكونات بنجاح');
    }

    public function store(StoreIngredientRequest $request)
    {
        $owner = $request->user();
        $validatedData = $request->validated();

        $ingredient = $this->ingredientService->store($owner, $validatedData);

        return ApiResponse::success([
            'ingredient' => new IngredientResource($ingredient),
        ], 'تم إضافة المكون بنجاح');
    }

    public function update(UpdateIngredientRequest $request, Ingredient $ingredient)
    {
        $validatedData = $request->validated();

        $ingredient = $this->ingredientService->update($ingredient, $validatedData);

        return ApiResponse::success([
            'ingredient' => new IngredientResource($ingredient),
        ], 'تم تعديل بيانات المكون بنجاح');
    }

    public function show(Ingredient $ingredient)
    {
        $ingredient = $this->ingredientService->show($ingredient);

        return ApiResponse::success([
            'ingredient' => new IngredientResource($ingredient),
        ], 'تم جلب بيانات المكون بنجاح');
    }
}