<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function create(CartItemRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $cart = Cart::query()
            ->firstOrCreate(['user_id' => $user->id]);

        $query = $cart->cartItems()->getQuery();

        $cartItem = $query->firstOrCreate([
            'cart_id' => $cart->id,
            'book_id' => $request->book_id,
        ], [
            'cart_id' => $cart->id,
            'book_id' => $request->book_id,
            'quantity' => $request->quantity,
            'is_checked' => true,
        ]);

        if (!$cartItem->wasRecentlyCreated) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        }

        return response()->json([
            'message' => 'добавлено в корзину',
            'data' => CartItemResource::make($cartItem),
            'code' => 200
        ], 200);;
    }

    public function list()
    {
        /**@var User $user */
        $user = Auth::user();

        $cart = $user?->cart;

        $cartItems = $cart?->cartItems()->isActive()->get();

        $data = filled($cartItems) ? CartItemResource::collection($cartItems) : [];

        return response()->json([
            'data' => $data,
            'code' => 200,
        ], 200);
    }

    public function deleteAll()
    {
        /**@var User $user */
        $user = Auth::user();

        $cart = $user->cart?->delete();

        $user->load('cart');//загрузим обновленые данные для модели

        return response()->json([
            'data' => 'cart delete',
            'code' => 200,
        ], 200);
    }

    public function deleteById($id)
    {
        /**@var User $user */
        $user = Auth::user();

        $query = $user->cart->cartItems()->getQuery();
        $cartItem = $query->firstWhere(['id' => $id]);

        if(filled($cartItem))
        {
            $cartItem->delete();
        }

        return response()->json([
            'data' => 'cart-item delete',
            'code' => 200,
        ], 200);
    }
}
