<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function sendSuccess($message){
    $response = [
        'error' => 0,
        'message' => $message . " SuccessFully Done",
    ];

    return response()->json($response);
}
}
