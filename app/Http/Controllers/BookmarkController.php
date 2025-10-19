<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function create(Request $request)
    {
        $userId = Auth::id();

        $bookMark = new Bookmark();
        $bookMark->user_id = $userId;
        $bookMark->book_id = $request->book_id;
        $bookMark->save();

        return response()->json(['message' => 'добавлено в избранное', 'product' => $bookMark, 'code' => 200], 200);
    }

    public function createGuest(Request $request)
    {
        //для не авторизванных сохраним в сессию
        $book = Book::query()->find($request->book_id);
        if (!$book) {
            abort(404);
        }

        session()->push('user.bookMarks', $book->id);

        // $book = session()->pull('user.bookMarks');получение

        return response()->json(['message' => 'добавлено в избранное', 'product' => $book, 'code' => 200], 200);
    }
}
