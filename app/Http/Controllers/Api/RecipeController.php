<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ReplaceRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\MenuItem;
use App\Services\RecipeService;
use App\Support\ApiResponse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function __construct(private RecipeService $recipeService)
    {
    }

    public function replace(ReplaceRecipeRequest $request, MenuItem $menuItem)
    {
        $validatedData = $request->validated();

        $menuItem = $this->recipeService->replace($menuItem, $validatedData);

        return ApiResponse::success([
            'recipe' => new RecipeResource($menuItem),
        ], 'تم استبدال الوصفة بنجاح');
    }

}
