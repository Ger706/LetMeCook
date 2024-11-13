<?php

namespace App\Http\Controllers;

use App\Models\FavoriteRecipe;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    public function calculateCalories($data): float
    {
        $totalCal = 0.0;
        foreach ($data as $nutrition) {
            if($nutrition['category_id'] === 7) {
                $totalCal += $nutrition['amount'] * 4;
            } else if ($nutrition['category_id'] === 8) {
                $totalCal += $nutrition['amount'] * 4;
            } else if ($nutrition['category_id'] === 10) {
                $totalCal += $nutrition['amount'] * 9;
            }
        }
        return $totalCal;
    }

    public function createRecipe(Request $request) {
        try {
            $req = $request->all();
            $path = $request->file('recipe_image')->store('images', 'public');

            $req['nutrition_info'] = json_decode($req['nutrition_info'], true);
            $req['recipe_step'] = json_decode($req['recipe_step'], true);
            $req['ingredient_list'] = json_decode($req['ingredient_list'], true);
            $calculateCalories = [];
            foreach ($req['nutrition_info'] as $item) {
                if (in_array($item['category_id'], [7, 8, 10])) {
                 $calculateCalories[] = $item;
                }
            }
            if (!empty($calculateCalories)) {
                $req['calories'] = $this->calculateCalories($calculateCalories);
            }
            $data = $this->encodeDecodeAttribute($req, 'encode');
            $data['recipe_image'] = Storage::disk('images')->url($path);
            $recipe = new Recipe();
            $recipe->fill($data);
            $recipe->save();
        } catch (Exception $e) {
            return $this->sendError('Failed to make recipe');
        }
        return $this->sendSuccess('Recipe successfully created');
    }

    public function getRecipeDetail(Request $req) {
        try {
            $data = $req->all();
            $result = Recipe::find($data['recipe_id']);

            if($result->user_id !== $data['user_id']) {
                $result->total_view += 1;
                $result->save();
            }
            if (!$result) {
                return $this->sendError('Recipe not found');
            }
//            $result->recipe_image = Storage::url($result->recipe_image);
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

            if (isset($data['user_id'])) {
                $result = $result->where('user_id','!=',$data['user_id']);
            }
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
           if (isset($data['category_id'])) {
            $recipeFiltered = [];
                foreach ($result as $key => $recipe) {
                    foreach ($recipe->nutrition_info as $nutrition) {
                        if ($nutrition->category_id === $data['category_id']) {
                            $recipeFiltered[] = $recipe;
                            break;
                        }
                    }
                }
                $result = $recipeFiltered;
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
    public function deleteRecipe($recipeId) {
        try {
            $recipe = Recipe::find($recipeId);
            if (!$recipe) {
                return $this->sendError('Recipe not found');
            }
            $recipe->delete();

        } catch (Exception $e) {
            return $this->sendError('Failed to delete recipe');
        }
        return $this->sendSuccess('Recipe successfully deleted');
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
                   $clause->whereNull('users.deleted_at');
                })
                ->join('recipes', static function ($clause) {
                    $clause->on('recipes.recipe_id', '=', 'favorite_recipes.recipe_id');
                    $clause->whereNull('recipes.deleted_at');
                })->
                select(
                    'recipes.recipe_id',
                    'recipes.recipe_name',
                    'recipes.recipe_image',
                    'recipes.recipe_description'
                )->get()->toArray();

            if ($result) {
               foreach ($result as $key => $recipe) {
                   $recipeArray = json_decode(json_encode($recipe), true);
                   $result[$key] = $this->encodeDecodeAttribute($recipeArray, 'decode');
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
            if ($recipe->user_id === $data['user_id']) {
                return $this->sendError('This is your own recipe silly');
            }
            $favoriteRecipe = FavoriteRecipe::where('user_id','=',$data['user_id'])
                ->where('recipe_id','=',$data['recipe_id'])
                ->whereNull('deleted_at')->get()->toArray();

            if ($favoriteRecipe) {
                return $this->sendError('Already Added as Favorite');
            } else {
                $favRecipe = new FavoriteRecipe();
                $favRecipe->fill($data);
                $favRecipe->save();
            }

        } catch (Exception $e) {
            return $this->sendError('Failed to add recipe as favorite');
        }
        return $this->sendSuccess('Recipe successfully added as favorite');
    }

    public function deleteFavoriteRecipes(Request $req) {
        try {
            $data = $req->all();
            $favoriteRecipe = FavoriteRecipe::where('user_id','=',$data['user_id'])
                ->where('recipe_id','=',$data['recipe_id'])
                ->whereNull('deleted_at');
            if (!$favoriteRecipe) {
                return $this->sendError('Failed to remove recipe from favorite');
            }
           $favoriteRecipe->delete();

        } catch (Exception $e) {
            return $this->sendError('Failed to remove recipe from favorite');
        }
        return $this->sendSuccess('Recipe successfully removed from favorite');
    }

    public function getPopularRecipes() {
        try {
            $recipes = Recipe::whereNull('deleted_at')
                ->select('recipes.recipe_name',
                    'recipes.recipe_image',
                    'recipes.recipe_id',
                    'recipes.calories',
                    'recipes.recipe_description',
                    'recipes.cook_time')
                ->orderBy('total_view', 'desc')
                ->take(4)
                ->get()
                ->toArray();
            if (!$recipes) {
                return $this->sendError('Recipes List Not Found');
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipes list');
        }
        return $this->sendResponseData($recipes);
    }

    public function getRecommendedRecipes() {
        try {
            $results = DB::table('recipes')
                ->select('recipes.recipe_name',
                    'recipes.recipe_image',
                    'recipes.recipe_id',
                    'recipes.calories',
                    'recipes.recipe_description',
                    'recipes.cook_time',
                    DB::raw('COUNT(favorite_recipes.recipe_id) as amount'))
                ->join('favorite_recipes', 'recipes.recipe_id', '=', 'favorite_recipes.recipe_id')
                ->whereNull('favorite_recipes.deleted_at')
                ->whereNull('recipes.deleted_at')
                ->groupBy('recipes.recipe_id', 'recipes.recipe_name', 'recipes.recipe_image', 'recipes.calories','recipes.recipe_description',
                    'recipes.cook_time')
                ->orderBy('amount', 'desc')
                ->limit(4)
                ->get()
                ->toArray();
            if (!$results) {
                return $this->sendError('Recipes List Not Found');
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipes list');
        }
        return $this->sendResponseData($results);
    }
}
