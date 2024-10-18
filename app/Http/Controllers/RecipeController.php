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
            $req = request()->all();
            $data = $this->encodeDecodeAttribute($req, 'encode');
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
            $result = $this->encodeDecodeAttribute($result, 'decode');
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipe');
        }
        return $result;
    }

    public function getRecipesByIngredient(Request $req) {
        try {
            $data = request()->all();
            $recipes = Recipe::whereRaw('json_length(ingredient_list) > 3')
                ->whereNull('deleted_at')->get();
            $ingredientList = Ingredient::whereIn('ingredient_id', $data['ingredient_list'])
                                ->select('ingredient_name')
                                ->pluck('ingredient_name')->toArray();
            $results = [];
            foreach ($recipes as $recipe) {
                if ($recipe->ingredient_list) {
                    $ingredients = json_decode($recipe->ingredient_list, true);
                    $ingredientNames = array_map(function($ingredient) {
                        return $ingredient['ingredient_name'];
                    }, $ingredients);
                    $matchingIngredients = array_intersect($ingredientList, $ingredientNames);

                    if (count($matchingIngredients) === 3) {
                        $results[] = $this->encodeDecodeAttribute($recipe, 'decode');
                    }
                }
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipes');
        }
        return $this->sendResponseData($results);
    }

    public function getRecipesByUser($userId) {
        try {
            $result = Recipe::where('user_id', $userId)->get()->toArray();
            if (!$result) {
                return $this->sendError('Recipe List not found');
            }
            foreach ($result as $key => $recipe) {
                $result[$key] = $this->encodeDecodeAttribute($recipe, 'decode');
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipe list');
        }
        return $this->sendResponseData($result);
    }

    public function encodeDecodeAttribute($data, $mode) {
        try {
            if ($mode === 'decode') {
                if (isset($data['ingredient_list'])) {
                    $data['ingredient_list'] = json_decode($data['ingredient_list']);
                }
                if (isset($data['nutrition_info'])) {
                    $data['nutrition_info'] = json_decode($data['nutrition_info']);
                }
                if(isset($data['recipe_step'])) {
                    $data['recipe_step'] = json_decode($data['recipe_step']);
                }
            } else {
                if (isset($data['ingredient_list'])) {
                    $data['ingredient_list'] = json_encode($data['ingredient_list']);
                }
                if (isset($data['nutrition_info'])) {
                    $data['nutrition_info'] = json_encode($data['nutrition_info']);
                }
                if (isset($data['recipe_step'])) {
                    $data['recipe_step'] = json_encode($data['recipe_step']);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $data;
    }
}
