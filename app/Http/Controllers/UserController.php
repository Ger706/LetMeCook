<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;

class UserController extends ResponseController
{
    public function createUser(Request $req){
        try {
            $data = $req->all();
            $usernameExist = User::where('username', $data['username'])->first();
            $emailExist = User::where('email',$data['email'])->first();
            if ($emailExist){
                return $this->sendError('Email Already Exist');
            }
            if ($usernameExist) {
                return $this->sendError('Username Already Exist');
            }
            $data['password'] = hash('sha256', $data['password']);
            $user = new User();
            $user->fill($data);
            $user->save();

        } catch (Exception $e) {
            return $this->sendError('Failed to Create User');
        }
        return $this->sendSuccess('User created Successfully');
    }

    public function getUser($userId) {
        try {
            $user = User::find($userId);
            if (!$user) {
                return $this->sendError("User not found");
            }
            $results = $user;

        } catch (Exception $e) {
            return $this->sendError('Failed to get user');
        }

        return $this->sendResponseData($results);
    }

    public function login(Request $req) {
        try {
            $data = $req->all();
            $user = User::where('username',$data['username'])->first();
            if (!$user){
                return $this->sendError('Account not found');
            }

            $hashedValue = hash('sha256', $data['password']);
            if ($hashedValue !== $user->password){
                return $this->sendError('Wrong Password');
            }

        } catch (Exception $e) {
            return $this->sendError('Failed to Login');
        }
        return $this->sendResponseData($user);
    }
    public function editUserInfo(Request $req) {
        try {
            $data = $req->all();
            $user = User::find($data['user_id']);
            if (!$user){
                return $this->sendError("User not found");
            }
            $user->fill($data);
            $user->save();
        } catch (Exception $e) {
            return $this->sendError('Failed to Edit User');
        }
        return $this->sendResponseData($user);
    }

    public function setUserAsPremium($userId) {
        try {
            $user = User::find($userId);
            if (!$user){
                return $this->sendError("User not found");
            }
            $user->is_premium = 1;
            $user->save();
        } catch (Exception $e) {
            return $this->sendError('Failed to set premium user');
        }
        return $this->sendResponseData($user);
    }
}
