<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteRecipe extends Model
{
    use HasFactory;
    protected $table      = 'favorite_recipes';
    protected $primaryKey = ['user_id','recipe_id'];
    protected $fillable = [
        'recipe_id',
        'user_id'
    ];
}
