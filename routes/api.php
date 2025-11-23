<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\Client\LoginController;
use App\Http\Controllers\Client\RegistrationController;
use App\Http\Controllers\OrderController;
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

Route::prefix('book')->group(function(){
    Route::get('{name}',[BookController::class,'searchByName']);
    Route::get('genre/{genreName}',[BookController::class,'searchByGenre']);
});


//для не авторизванных сохраним в сессию
Route::prefix('guest')->middleware('guest')->group(function () {
    Route::prefix('bookmark')->group(function () {
        Route::post('add', [BookmarkController::class, 'createGuest']);
        Route::get('list', [BookmarkController::class, 'listGuest']);
        Route::delete('delete/{id}', [BookmarkController::class, 'deleteByIdGuest']);
        Route::delete('delete', [BookmarkController::class, 'deleteAllGuest']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('bookmark')->group(function () {
        Route::post('add', [BookmarkController::class, 'create']);
        Route::get('list', [BookmarkController::class, 'list']);
        Route::delete('delete/{id}', [BookmarkController::class, 'deleteById']);
        Route::delete('delete', [BookmarkController::class, 'deleteAll']);
    });

    Route::prefix('cart')->group(function () {
        Route::post('add', [CartController::class, 'create']);
        Route::get('list', [CartController::class, 'list']);
        Route::delete('delete/{id}', [CartController::class, 'deleteById']);
        Route::delete('delete', [CartController::class, 'deleteAll']);
    });

    Route::prefix('order')->group(function () {
        Route::post('add', [OrderController::class, 'create']);
        Route::get('list', [OrderController::class, 'list']);
        Route::post('cancel/{id}', [OrderController::class, 'cancellById']);
    });

    Route::prefix('balance')->group(function () {
        Route::post('add', [CashAccountController::class, 'create']);
    });
});

Route::prefix('stripe')->group(function () {
    Route::post('update-status', [CashAccountController::class, 'accountStatusUpdate']);
});

Route::prefix('balance')->group(function () {
    Route::get('succes', [CashAccountController::class, 'succes']);
    Route::get('cancel', [CashAccountController::class, 'cancel']);
});
