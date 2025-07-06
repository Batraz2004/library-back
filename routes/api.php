<?php

use App\Http\Controllers\Client\LoginController;
use App\Http\Controllers\Client\RegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/test', function (Request $req) {
    return response()->json([
        'code' => 200,
        'message' => 'its work',
        'req_contebt' => $req->getContent()
    ]);
});
Route::get('/authTest',function(){
    return response()->json([
        'code' => 200,
         'message' => 'its work',
    ]);
})->middleware('auth:sanctum');

// Route::post('user',);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sunctum');

Route::post('registration', [RegistrationController::class, 'createUser']);
Route::post('login', [LoginController::class, 'login']);
