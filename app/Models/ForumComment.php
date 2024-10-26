<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    use HasFactory;

    protected $table = 'forum_comment';
    protected $primaryKey = 'forum_comment_id';

    protected $fillable = [
        'recipe_id',
        'user_id',
        'comment',
        'likes'
    ];
}
