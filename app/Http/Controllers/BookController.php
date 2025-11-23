<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function searchByName($name)
    {
        $books = Book::query()->where('title', 'like', "%$name%")->get();

        return response()->json([
            'message' => 'найденные совпадения',
            'data' => BookResource::collection($books),
        ], 200);
    }

    public function searchByGenre($genreName)
    {
        $books = Book::query()->whereHas('genres', function ($query) use ($genreName) {
            $query->where('title', 'LIKE', "%$genreName%"); 
        })->get();

        return response()->json([
            'message' => 'найденные совпадения',
            'data' => BookResource::collection($books),
        ], 200);
    }
}
