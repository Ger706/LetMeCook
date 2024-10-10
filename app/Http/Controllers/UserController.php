<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Mockery\Exception;

class UserController extends ResponseController
{
    public function createUser(Request $req){
        try {
//            $user = new User();
//            $user->fill($req->all());
//            $user->save();

        } catch (Exception $e) {
            throw $e;
        }
        return $this->sendSuccess('User created');
    }
}
