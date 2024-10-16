<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Mockery\Exception;

class RecipeController extends ResponseController
{
    public function createRecipe() {
        try {
            $data = request()->all();
            $recipe = new Recipe();
            $recipe->fill($data);
            $recipe->save();
        } catch (Exception $e) {
            return $this->sendError('Failed to make recipe');
        }
        return $this->sendSuccess('Recipe successfully created');
    }

    public function getRecipeDetail($recipeId) {
        try {
            $result = Recipe::find($recipeId);
            if (!$result) {
                return $this->sendError('Recipe not found');
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipe');
        }
        return $result;
    }
    public function getRecipesByIngredient(Request $req) {
        try {
            $data = request()->all();
            $recipes = Recipe::whereRaw('json_length(ingredient_list) > 3');
            $ingredientList = Ingredient::whereIn('ingredient_list', $data['ingredient_list'])
                                ->select('ingredient_name')
                                ->pluck('ingredient_name');
            $results = [];
            foreach ($recipes as $recipe) {
                if ($recipe->ingredient_list) {
                    $ingredients = json_decode($recipe->ingredient_list, true);

                    $ingredientNames = array_map(function($ingredient) {
                        return $ingredient['ingredient_name'];
                    }, $ingredients);
                    $matchingIngredients = array_intersect($ingredientList, $ingredientNames);

                    if (count($matchingIngredients) === 3) {
                        $results[] = $recipe;
                    }
                }
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipes');
        }
        return $results;
    }
}
