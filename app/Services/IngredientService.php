<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Staff;
use Illuminate\Support\Collection;

class IngredientService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function index(): Collection
    {
        return Ingredient::all();
    }

    public function store(Staff $owner, array $data): Ingredient
    {
        $data['restaurant_id'] = $owner->restaurant_id;

        return Ingredient::create($data);
    }

    public function show(Ingredient $ingredient): Ingredient
    {
        return $ingredient;
    }

    public function update(Ingredient $ingredient, array $data): Ingredient
    {
        $ingredient->update($data);

        return $ingredient;
    }

}