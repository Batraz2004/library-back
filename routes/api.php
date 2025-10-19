<?php

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

Route::prefix('quest')->middleware('guest')->group(function(){
    Route::prefix('bookmarks')->group(function () {
        Route::post('add', function (Request $request) {
            //для не авторизванных сохраним в сессию
            $book = Book::query()->find($request->book_id);
            if (!$book) {
                abort(404);
            }

            session()->push('user.bookMarks', $book->id);

            // $book = session()->pull('user.bookMarks');получение

            return response()->json(['message' => 'добавлено в избранное', 'product' => $book, 'code' => 200], 200);
        });
    });
});

Route::middleware('auth:sanctum')->group(function(){
    Route::prefix('bookmarks')->group(function () {
        Route::post('add', function (Request $request) {

            $userId = Auth::id();

            $bookMark = new Bookmark();
            $bookMark->user_id = $userId;
            $bookMark->book_id = $request->book_id;
            $bookMark->save();

            return response()->json(['message' => 'добавлено в избранное', 'product' => $bookMark, 'code' => 200], 200);
        });
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