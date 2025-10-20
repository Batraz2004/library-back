<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\Client\LoginController;
use App\Http\Controllers\Client\RegistrationController;
use App\Models\Book;
use App\Models\Bookmark;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/test', function (Request $req) {
    return response()->json([
        'code' => 200,
        'message' => 'You authorize!',
        'req_contebt' => $req->getContent()
    ]);
});
Route::get('/authTest', function () {
    return response()->json([
        'code' => 200,
        'message' => 'You authorize!',
    ]);
})->middleware('auth:sanctum');

// Route::post('user',);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('registration', [RegistrationController::class, 'createUser']);
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

//для не авторизванных сохраним в сессию
Route::prefix('quest')->middleware('guest')->group(function(){
    Route::prefix('bookmarks')->group(function () {
        Route::post('add', [BookmarkController::class,'createGuest']);
        Route::get('list', [BookmarkController::class,'listGuest']);
        Route::delete('delete/{id}', [BookmarkController::class,'deleteGuest']);
    });
});

Route::middleware('auth:sanctum')->group(function(){
    Route::prefix('bookmarks')->group(function () {
        Route::post('add', [BookmarkController::class,'create']);
    });
});

// Route::post('add', function (Request $request) {

//             $userId = Auth::id();

//             $cart = new Cart();
//             $cart->user_id = $userId;
//             $cart->save();

//             $cartItems = new CartItem();

//             foreach

//         });