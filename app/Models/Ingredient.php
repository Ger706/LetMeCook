<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Ingredient extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table      = 'ingredients';
    protected $primaryKey = 'ingredient_id';
    protected $fillable = [
        'ingredient_name',
        'ingredient_image',
        'ingredient_description',
        'amount_per_unit',
        'unit'
    ];

}
