<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatusEnum;
use App\Models\CashAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Webhook;

class CashAccountController extends Controller
{
    //если транзакцию нужно сделать на сайте а не через шлюз 
    // public function create(Request $request)
    // {
    //     Stripe::setApiKey(config('stripe.stripe_sk'));

    //     // //формирование прямо в беке запрещено
    //     // $token = Token::create([
    //     //     'card' => [
    //     //         'number' => $request->card_number,
    //     //         'exp_month' => $request->exp_month,
    //     //         'exp_year' => $request->exp_year,
    //     //         'cvc' => $request->cvc,
    //     //     ],
    //     // ]);

    //     $payment = PaymentIntent::create([
    //         "amount" => $request->price * 100,
    //         "currency" => "rub",
    //         // "source" => $token,
    //         "source" => $request->token,
    //         "description" => "Test transaction"
    //     ]);

    //     //логика после создания
    // }

    //шлюз страйпа
    public function create(Request $request)
    {
        /**@var User $user*/
        $user = Auth::user();

        $amount = $request->amount;
        $currency = $request->currency;

        /**@var CashAccount $balanceAccount */
        $balanceAccount = $user->account()?->firstOrCreate(['user_id' => $user->id], [
            'user_id' => $user->id,
            'balance' => $amount,
            'currency' => $currency,
            'status' => AccountStatusEnum::awaiting->value,
        ]);

        // if(!$balanceAccount->wasRecentlyCreated) // это будет создаваться в обработке вебхука
        // {
        //     $balanceAccount->balance += $amount;
        //     $balanceAccount->save();
        // }

        Stripe::setApiKey(config('stripe.stripe_sk'));

        $checkoutSession = Session::create([
            'metadata' => [
                'subscribe_id' => $balanceAccount->id, // вот здесь привязываем id подписки
                'amount' => $amount,
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'user-balance', // название подписки
                    ],
                    'unit_amount' => $amount * 100, // или цена из продукта
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => URL::to('/api/balance/succes'),
            'cancel_url' => URL::to('/api/balance/cancel')
        ]);

        return response()->json([
            'data' => $balanceAccount,
            'payment_url' => $checkoutSession->url
        ], 200);
    }

    public function accountStatusUpdate(Request $request)
    {
        try {
            $secretWebhookKey = config('stripe.stripe_webhook_sk');
            $signature = $request->header('stripe-signature');
            $requestBody = $request->getContent();

            $event = Webhook::constructEvent($requestBody, $signature, $secretWebhookKey);
        } catch (\Exception $ex) {
            return response()->json(['webhook error = ' . $ex->getMessage()], 400);
        }

        $metadata = $request->toArray()['data']['object']['metadata'] ?? [];

        if (blank($metadata)) {
            Log::error('metadata is empty');
            return response()->json(['user not found'], 400);
        }

        //обработка webhook для payment_link
        // if (
        //     array_key_exists('people_count', $metadata)
        //     && array_key_exists('period', $metadata)
        //     && array_key_exists('email', $metadata)
        // ) {
        //     return $this->webhookPaymentLinkHandling($event, $metadata, $request);
        // } else {
            //обработка вебхука при офрмлении подписки через шлюз а сайте:
            $this->webhookGateHandling($event, $metadata);
        // }
    }

    private function webhookGateHandling($event, $metadata)
    {

        $accountId = $metadata['subscribe_id'] ?? null;

        if (is_null($accountId)) {
            Log::error('Account ID not found in metadata', [
                'available_metadata' => $metadata,
            ]);
            return response()->json(['account_id is null'], 400);
        }

        $account = CashAccount::query()->find($accountId);

        if (
            $event->type === "checkout.session.completed" ||
            $event->type === "checkout.session.async_payment_succeeded"
        ) {
            //логика при успешном зачислении
            $account->status = AccountStatusEnum::active->value;
            //при многочилсенном пополнении счета
            if(!$account->wasRecentlyCreated)
            {
                $account->balance += $metadata['amount'] ?? 0;
            }
        } 
           //логика при не успешном зачислении
        else if (
            $event->type === "checkout.session.expired"
        ) {
            $account->status = AccountStatusEnum::expired->value;
        }
        else if (
            $event->type === "checkout.session.async_payment_failed"
        ) {
           
            $account->status = AccountStatusEnum::failed->value;
        }
    }

    // private function webhookPaymentLinkHandling($event, $metadata, $request)
    // {
    //     if (
    //         $event->type === "checkout.session.completed" ||
    //         $event->type === "checkout.session.async_payment_succeeded"
    //     ) {
    //         //логика при успешщно зачислении
    //     }
    // }

    public function succes()
    {
        /**@var User $user */
        $user = Auth::user();
        $balance = $user->account()?->get();

        return response()->json(['data' => $balance], 200);
    }

    public function cancell()
    {
        return response()->json(['message' => 'оплата отменена'], 200);
    }
}
