<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function sendSuccess($message){
        $response = [
            'error' => 0,
            'message' => $message,
        ];
        return response()->json($response);
    }

    public function sendError($message){
        $response = [
            'error' => 1,
            'message' => $message,
        ];
        return response()->json($response);
    }
    public function sendResponseData($data, $messagge = null){
        $response = [
            'error' => 0,
            'data' => $data,
            'message' => $messagge,
        ];
        return response()->json($response);
    }
}
