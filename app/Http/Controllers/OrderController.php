<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Models\CartItem;
use App\Models\CashAccount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        //баланс пользователя
        $userBalance = $user->account->total_balance;

        //выбранные товары в корзине
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $cartItemsQuery = $user->cart->cartItems()->getQuery();

        /** @var \Illuminate\Database\Eloquent\Builder<CartItem> $query */
        $checkedCartItems = $cartItemsQuery->isActive()
            ->isChecked()
            ->with('book')
            ->withSum(['book'], 'price')
            ->get();

        $totalPrice = $checkedCartItems->sum(function ($item) {
            /** @var CartItem $item */
            return $item->book_sum_price * $item->quantity;
        });

        if ($userBalance < $totalPrice) {
            return response()->json(
                [
                    'message' => 'не достаточно средств',
                    'code' => 400
                ],
                400
            );
        } else {
            //процесс оформления заказа
            try {
                DB::beginTransaction();

                $order = Order::create([
                    'user_id'    => $user->id,
                    'address'    => $request->address,
                    'full_price' => $totalPrice,
                    'phone'      => $request->phone,
                ]);

                $checkedCartItems->each(function ($item, $key) use ($order) {
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'book_id'  => $item->book_id,
                        'quantity' => $item->quantity,
                        'price' => $item->book_sum_price,
                    ]);

                    $orderItem->save();

                    $item->delete();
                });

                $responce = OrderStatusEnum::active->value;

                DB::commit();
            } catch (Throwable $th) {
                DB::rollBack();

                Log::debug("произошла ошибка:" . $th->getMessage() . " строка:" . $th->getLine());

                $responce = OrderStatusEnum::error->value;
            }

            return response()->json(
                [
                    'status' => $responce,
                    'code' => 200
                ],
                200
            );
        }
    }

    public function list()
    {
        try{
            /** @var User $user */
            $user = Auth::user();

            /** @method Builder query() */
            $order = $user->query()
                ->orders()
                ->with('items')
                ->get();

            return response()->json([
                'data' => $order,
                'code' => 200,
            ], 200);
        }
        catch(Throwable $th){
            Log::debug("произошла ошибка:" . $th->getMessage() . " строка:" . $th->getLine());

            return response()->json([
                'message' => "произошла ошибка:".$th->getMessage(),
                'code' => $th->getCode(),
            ], $th->getCode());
        }
    }
}
