<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\IngredientCategory;
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
                    'ingredient_category.nutrition AS amount',
                    DB::raw('CONCAT(ingredients.amount_per_unit, " ", ingredients.unit) AS amount_per_unit'),
                    'categories.category_name',
                    DB::raw('CONCAT(ingredient_category.nutrition, " ", ingredient_category.unit) AS nutrition_per_unit'),
                    'ingredients.ingredient_image',
                'categories.category_id',
                'ingredients.ingredient_description')
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
            $caloriesByIngredient = [];

            foreach ($ingredients as $ingredient) {
                $ingredient->calories = $this->calculateCalories($ingredient);
                $key = $ingredient->ingredient_id . '_' . $ingredient->ingredient_name;

                // Group calories by the unique key
                if (!isset($caloriesByIngredient[$key])) {
                    $caloriesByIngredient[$key] = $ingredient->calories;
                } else {
                    $caloriesByIngredient[$key] += $ingredient->calories;
                }
            }

            foreach ($ingredients as $ingredient) {
                $key = $ingredient->ingredient_id . '_' . $ingredient->ingredient_name;
                $ingredient->calories = $caloriesByIngredient[$key];
            }
            if ($ingredients->isEmpty()) {
                return $this->sendError('No Ingredients Available');
            }
        } catch (Exception $e) {
            return $this->sendError('Error Getting Ingredients');
        }
        return $this->sendResponseData($ingredients);
    }
    public function getIngredientCategoryList(Request $request) {
        try {
            $categories = Category::whereNull('deleted_at')->get()->toArray();
            if (!$categories) {
                return $this->sendError('No Ingredients Available');
            }
            $result = [];
            foreach ($categories as $category) {
                if (strpos($category['category_name'], 'Vitamin') !== false) {
                    $vitaminCategoryIds[] = $category['category_id'];
                } else {
                    $result[] = $category;
                }
            }
            if (!empty($vitaminCategoryIds)) {
                $result[] = [
                    'category_id' => $vitaminCategoryIds,
                    'category_name' => 'Vitamin',
                    'category_image' => '../src/Images/vitamin-icon.png'
                ];
            }
        } catch (Exception $e) {
            return $this->sendSuccess('Error Getting Ingredient Categories');
        }
        return $this->sendResponseData($result);
    }
    public function calculateCalories($data): int
    {
        $totalCal = 0;
            if($data->category_id === 7) {
                $totalCal = $data->amount * 4;
            } else if ($data->category_id === 8) {
                $totalCal = $data->amount * 4;
            } else if ($data->category_id === 10) {
                $totalCal = $data->amount * 9;
            }

        return $totalCal;
    }
}
