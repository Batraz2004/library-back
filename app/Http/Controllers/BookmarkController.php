<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BookmarkController extends Controller
{
    public function create(Request $request)
    {
        $userId = Auth::id();
        if (!Book::find($request->book_id)) {
            return response()->json(['message' => 'кига не найдена', 'code' => 404], 404);
        }
        $bookMark = new Bookmark();
        $bookMark->user_id = $userId;
        $bookMark->book_id = $request->book_id;
        $bookMark->save();


        return response()->json(['message' => 'добавлено в избранное', 'product' => $bookMark, 'code' => 200], 200);
    }

    public function createGuest(Request $request)
    {
        $book = Book::query()->find($request->book_id);
        if (!$book) {
            abort(404);
        }


        $bookmarks = Session::pull('user.bookmarks') ?? [];

        if (filled($bookmarks) && array_key_exists($request->book_id, $bookmarks)) {
            $bookmarks[$request->book_id]['quantity'] += 1;
        } else {
            if ($request->quantity > $book->count)
                return response()->json(['message' => 'такого объема нет в наличии', 'code' => 200], 200);

            $bookmark = [
                'quantity' => intval($request->quantity),
                'author' => $book->author,
                'price' => $book->price,
            ];

            $bookmarks[$request->book_id] = $bookmark;
        }


        Session::put('user.bookmarks', $bookmarks);

        Session::save();

        return response()->json(['message' => 'добавлено в избранное', 'product' => $bookmarks, 'code' => 200], 200);
    }

    public function listGuest()
    {
        $bookmarks = Session::get('user.bookmarks') ?? [];
        return response()->json(['bookmarks' => $bookmarks, 'code' => 200], 200);
    }
    public function deleteGuest($id)
    {
        $bookmarks = Session::pull('user.bookmarks');
        unset($bookmarks[$id]);

        Session::put($bookmarks);

        Session::save();

        return response()->json(['message' => 'удалено', 'code' => 200], 200);
    }

    public function deleteAllGuest()
    {
        $bookmarks = Session::pull('user.bookmarks');

        Session::forget('user.bookMarks');
        Session::flush();
        Session::regenerate();

        return response()->json(['message' => 'удалено', 'code' => 200], 200);
    }
}
