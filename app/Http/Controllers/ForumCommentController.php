<?php

namespace App\Http\Controllers;

use App\Models\ForumComment;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class ForumCommentController extends ResponseController
{
    public function getCommentsForRecipe(Request $request) {
        try {
            $data = $request->all();
            $result = DB::table('forum_comment')
                ->join('users', 'forum_comment.user_id', '=', 'users.user_id')
                ->select(
                    'forum_comment.forum_comment_id',
                'forum_comment.likes',
                    DB::raw("SUM(JSON_LENGTH(forum_comment.likes)) as like_amount"),
                'forum_comment.comment',
                'forum_comment.user_id',
                'users.username',
                'users.is_expert')
                ->groupBy('forum_comment.forum_comment_id',
                'forum_comment.likes',
                'forum_comment.comment',
                'forum_comment.user_id',
                'users.username',
                'users.is_expert')
                ->where('forum_comment.recipe_id', '=', $data['recipe_id'])->get()->toArray();

            if (!$result) {
                return $this->sendError('Failed to get recipe forum');
            }
            foreach ($result as $key => $value) {
                $value->likes = json_decode($value->likes);
                foreach($value->likes as $like) {
                    if($like === $data['user_id']) {
                       $result[$key]->already_like = true;
                        break;
                    }else {
                        $result[$key]->already_like = false;
                    }
                }
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get recipe forum');
        }
        return $this->sendResponseData($result);
    }

    public function commentForum(Request $request) {
        try {
            $data = $request->all();
            $forumComment = new ForumComment();
            $forumComment->fill($data);
            $forumComment->likes = "[]";
            $forumComment->save();

        } catch (Exception $e) {
            return $this->sendError('Failed to comment forum');
        }
        return $this->sendSuccess('Forum Commented Successfully');
    }

    public function likeDislikeForum(Request $request) {
        try {
            $data = $request->all();
            $forumComment = ForumComment::find($data['forum_comment_id']);
            $forumComment->likes = json_decode($forumComment->likes);
            if($data['action'] == 'like') {
                $forumComment->likes = array_merge($forumComment->likes, [$data['user_id']]);
            } else if($data['action'] == 'dislike') {
                $forumComment->likes = array_filter($forumComment->likes, function ($id) use ($data) {
                    return $id != $data['user_id'];
                });
            }
            $forumComment->save();
        } catch (Exception $e) {
            return $this->sendError('Failed to like/dislike forum');
        }
        return $this->sendResponseData($forumComment);
    }
}
