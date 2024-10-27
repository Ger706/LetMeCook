<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Recipe extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table      = 'recipes';
    protected $primaryKey = 'recipe_id';
    protected $fillable = [
        'recipe_name',
        'ingredient_list',
        'recipe_step',
        'recipe_image',
        'nutrition_info',
        'calories',
        'user_id',
        'total_view'
    ];

}
