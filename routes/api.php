<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/test', function (Request $req) {
    return response()->json([
        'code' => 200,
        'message' => 'its work',
        'req_contebt' => $req->getContent()
    ]);
});

// Route::post('user',);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
