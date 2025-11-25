<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookMarkRequest;
use App\Http\Resources\BookMarkCollection;
use App\Http\Resources\BookMarkResource;
use App\Models\Book;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BookmarkController extends Controller
{
    public function create(BookMarkRequest $request)
    {
        $userId = Auth::id();
        $bookId = $request->book_id;
        $quantity = $request->quantity;

        $data = $request->getData();
        $data['user_id'] = $userId;

        $bookmark = Bookmark::query()->firstOrCreate(
            [
                'user_id' => $userId,
                'book_id' => $bookId
            ],
            $request->getData()
        );

        if (!$bookmark->wasRecentlyCreated) {
            $bookmark->quantity += $quantity;
            $bookmark->save();
        }

        return response()->json([
            'message' => 'добавлено в избранное',
            'data' =>  BookMarkResource::make($bookmark),
            'code' => 200
        ], 200);
    }

    public function list()
    {
        /**@var User $user */
        $user = Auth::user();
        $bookmarks = $user?->bookmarks()->isActive()->get();

        return response()
            ->json([
                    'data' => BookMarkResource::collection($bookmarks)
                ],
                200
            );
    }

    public function deleteAll()
    {
        $user = Auth::user();
        $bookmarks = $user?->bookmarks->each(function ($item) {
            $item?->delete();
        });

        return response()
            ->json(['data' => 'все записи удалены'], 200);
    }

    public function deleteById($id)
    {
        $bookmark = Bookmark::find($id);
        $bookmark?->delete();

        return response()
            ->json(['data' => 'запись удалена'], 200);
    }


    public function createGuest(BookMarkRequest $request)
    {
        $book = Book::query()->find($request->book_id);
        if (!$book) {
            abort(404);
        }

        $bookmarks = Session::pull('user.bookmarks') ?? [];

        if (filled($bookmarks) && array_key_exists($request->book_id, $bookmarks)) {
            $bookmarks[$request->book_id]['quantity'] += intval($request->quantity);
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

        return response()->json([
            'message' => 'добавлено в избранное',
            'data' => $bookmarks,
            'code' => 200
        ], 200);
    }

    public function listGuest()
    {
        $bookmarks = Session::get('user.bookmarks') ?? [];
        return response()->json([
            'data' => $bookmarks,
            'code' => 200
        ], 200);
    }

    public function deleteByIdGuest($id)
    {
        $bookmarks = Session::pull('user.bookmarks');
        unset($bookmarks[$id]);

        Session::put('user.bookmarks', $bookmarks);
        Session::save();

        return response()->json([
            'message' => 'удалено',
            'code' => 200
        ], 200);
    }

    public function deleteAllGuest()
    {
        $bookmarks = Session::pull('user.bookmarks');

        Session::forget('user.bookMarks');
        Session::flush();
        Session::regenerate();

        return response()->json([
            'message' => 'удалено',
            'code' => 200
        ], 200);
    }
}
