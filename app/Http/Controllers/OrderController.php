<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentAccountStatusEnum;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\CashAccount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    public function create(OrderCreateRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        //баланс пользователя
        /** @var CashAccount $userAccount */

        $userAccount = $user->account;
        $userBalance = $userAccount?->total_balance;

        if (blank($userBalance) || $userAccount->status != PaymentAccountStatusEnum::active->value) {
            abort(400, 'счет не активен');
        }
    
        //выбранные товары в корзине
        /** @var CartItem $checkedCartItems */
        $checkedCartItems = $user->cart?->cartItems()
            ->isActive()
            ->isChecked()
            ->with('book')
            ->withSum(['book'], 'price')
            ->get();

        if (blank($checkedCartItems)) {
            abort(404, 'корзина пуста');
        }

        $totalPrice = $checkedCartItems->sum(function ($item) {
            /** @var CartItem $item */
            return $item->book_sum_price * $item->quantity;
        });
        if (blank($userBalance) || $userBalance < $totalPrice || blank($checkedCartItems)) {
            abort(400, 'не фозможно оформить заказ');
        } else {
            //процесс оформления заказа
            try {
                DB::beginTransaction();

                $userAccount->total_balance -= $totalPrice;
                $userAccount->save();

                $order = Order::create([
                    'user_id'    => $user->id,
                    'address'    => $request->address,
                    'full_price' => $totalPrice,
                    'phone'      => $request->phone,
                ]);

                $order->refresh();

                $checkedCartItems->each(function ($item, $key) use ($order) {
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'book_id'  => $item->book_id,
                        'quantity' => $item->quantity,
                        'price' => $item->book_sum_price,
                    ]);

                    $item->delete();
                });

                $response = OrderStatusEnum::active->value;

                $order->load('items');

                DB::commit();

                return response()->json(
                    [
                        'data' => OrderResource::make($order),
                        'status' => $response,
                        'code' => 200
                    ],
                    200
                );
            } catch (Throwable $th) {
                DB::rollBack();

                Log::debug("произошла ошибка:" . $th->getMessage() . " строка:" . $th->getLine());

                $response = OrderStatusEnum::error->value;

                return response()->json(
                    [
                        'status' => $response,
                        'code' => $th->getCode()
                    ],
                    $th->getCode()
                );
            }
        }
    }

    public function list()
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            /** @method Builder query() */
            $order = $user->orders()
                ->with('items')
                ->get();

            return response()->json([
                'data' => OrderResource::collection($order),
                'code' => 200,
            ], 200);
        } catch (Throwable $th) {
            Log::debug("произошла ошибка:" . $th->getMessage() . " строка:" . $th->getLine());

            return response()->json([
                'message' => "произошла ошибка:" . $th->getMessage(),
                'code' => $th->getCode(),
            ], $th->getCode());
        }
    }

    public function cancellById($id)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            //баланс пользователя
            $userAccount = $user->account;

            $order = $user
                ->orders()
                ->getQuery()
                ->firstWhere(['id' => $id]);

            if ($order->status === OrderStatusEnum::active->value) {
                DB::beginTransaction();

                $order->status = OrderStatusEnum::cancelled->value;
                $userAccount->total_balance += $order->full_price;

                $order->save();
                $userAccount->save();

                DB::commit();
            }

            return response()->json([
                'order' => OrderResource::make($order),
                'message' => "заказ отменен",
                'code' => 200,
            ], 200);
        } catch (Throwable $th) {
            DB::rollBack();

            Log::debug("произошла ошибка:" . $th->getMessage() . " строка:" . $th->getLine());

            return response()->json([
                'message' => "произошла ошибка:" . $th->getMessage(),
                'code' => $th->getCode(),
            ], $th->getCode());
        }
    }
}
