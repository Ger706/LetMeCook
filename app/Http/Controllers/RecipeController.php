<?php

namespace App\Http\Controllers;

use App\Models\FavoriteRecipe;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class RecipeController extends ResponseController
{
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
    public function getRecipeList(Request $request) {
        try {
            $data = $request->all();
            $result = Recipe::whereNull('deleted_at');
            if (isset($data['search'])) {
                $result = $result->where('recipe_name', 'like', '%' . $data['search'] . '%');
            }
            if (isset($data['orderBy']) && isset($data['order'])) {
                $result = $result->orderBy($data['orderBy'], $data['order']);
            }
            if (isset($data['limit']) && isset($data['offset'])) {
                $result = $result->skip($data['offset'])->take($data['limit']);
            }
            $result = $result->get();
            if ($result->isEmpty()) {
                return $this->sendError('Recipe not found');
            }
            foreach ($result as $key => $recipe) {
                $result[$key] = $this->encodeDecodeAttribute($recipe, 'decode');
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipe');
        }
        return $this->sendResponseData($result);
    }

    public function getRecipesByIngredient(Request $req) {
        try {
            $data = $req->all();
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

    public function getUserFavoriteRecipes($userId) {
        try {
            $result = DB::table('favorite_recipes')
                ->join('users',static function ($clause) use ($userId) {
                    $clause->on('users.user_id', '=', 'favorite_recipes.user_id');
                   $clause->where('users.user_id','=', $userId);
                   $clause->whereNull('deleted_at');
                })
                ->join('recipes', static function ($clause) {
                    $clause->on('recipes.recipe_id', '=', 'favorite_recipes.recipe_id');
                    $clause->whereNull('deleted_at');
                })->get();

            if (!$result->isEmpty()) {
               foreach ($result as $key => $recipe) {
                   $result[$key] = $this->encodeDecodeAttribute($recipe, 'decode');
               }
            } else {
                return $this->sendError('No Favorite Recipes Found');
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipe list');
        }
        return $this->sendResponseData($result);
    }

    public function setRecipeAsFavorite(Request $req) {
        try {
            $data = $req->all();

            if (!isset($data['recipe_id']) || !isset($data['user_id'])) {
                return $this->sendError('Failed to add recipe as favorite');
            }
            $recipe = Recipe::where('recipe_id', $data['recipe_id'])
            ->whereNull('deleted_at')
            ->first();

            if (!$recipe) {
                return $this->sendError('Recipe does not exist');
            }
            $favRecipe = new FavoriteRecipe();
            $favRecipe->fill($data);
            $favRecipe->save();
        } catch (Exception $e) {
            return $this->sendError('Failed to add recipe as favorite');
        }
        return $this->sendSuccess('Recipe successfully added as favorite');
    }

    public function deleteFavoriteRecipes($favoriteRecipeId) {
        try {
            if (!$favoriteRecipeId) {
                return $this->sendError('Failed to remove recipe from favorite');
            }
            $favRecipe = FavoriteRecipe::find($favoriteRecipeId);
            $favRecipe->delete();

            if (!isset($data['recipe_id']) || !isset($data['user_id'])) {
                return $this->sendError('Failed to add recipe as favorite');
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to remove recipe from favorite');
        }
        return $this->sendSuccess('Recipe successfully removed from favorite');
    }
}
