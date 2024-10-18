<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class IngredientController extends ResponseController
{
    public function getIngredientList(Request $request) {
        try {
            $data = $request->all();
            $ingredients = DB::table('ingredients')
                ->join('ingredient_category', 'ingredients.ingredient_id', '=', 'ingredient_category.ingredient_id')
                ->join('categories', 'ingredient_category.category_id', '=', 'categories.category_id')
                ->select(
                    'ingredients.ingredient_id',
                    'ingredients.ingredient_name',
                    DB::raw('CONCAT(ingredients.amount_per_unit, " ", ingredients.unit) AS amount_per_unit'),
                    'categories.category_name',
                    DB::raw('CONCAT(ingredient_category.nutrition, " ", ingredient_category.unit) AS nutrition_per_unit'))
                ->whereNull('ingredients.deleted_at')
                ->whereNull('ingredient_category.deleted_at');

            if (isset($data['category_id'])) {
                $ingredients = $ingredients->where('ingredient_category.category_id', $data['category_id']);
            }
            if (isset($data['ingredient_id'])) {
                $ingredients = $ingredients->where('ingredients.ingredient_id', $data['ingredient_id']);
            }
            if (isset($data['order_by'])) {
                $ingredients = $ingredients->orderBy($data['order_by']);
            }
            $ingredients = $ingredients->get();

            if ($ingredients->isEmpty()) {
                return $this->sendError('No Ingredients Available');
            }
        } catch (Exception $e) {
            return $this->sendError('Error Getting Ingredients');
        }
        return $this->sendResponseData($ingredients);
    }
}
