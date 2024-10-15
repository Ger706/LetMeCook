<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class IngredientCategory
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table      = 'ingredient_category';
    protected $primaryKey = ['ingredient_id', 'category_id'];
    protected $fillable = [
        'ingredient_id',
        'category_id',
        'nutrition',
        'unit'
    ];

}
