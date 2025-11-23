<?php

namespace App\Http\Controllers;

use App\Enums\PaymentAccountStatusEnum;
use App\Enums\PaymentTransactionStatusEnum;
use App\Models\CashAccount;
use App\Models\Transaction;
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
    //шлюз страйпа
    public function create(Request $request)
    {
        /**@var User $user*/
        $user = Auth::user();

        $amount = $request->amount;
        $currency = $request->currency;

        //открытие счета:
        /**@var CashAccount $balanceAccount */
        $balanceAccount = $user->account()?->firstOrCreate(['user_id' => $user->id], [
            'user_id' => $user->id,
            'currency' => $currency,
            'status' => PaymentAccountStatusEnum::awaiting->value,//при первом заполнении станет active
        ]);

        if (!$balanceAccount->wasRecentlyCreated) {
            if ($currency != $balanceAccount->currency)
                abort(400, 'указанная валюта должна совпадать с валютой счета');
        }

        //сохраним запись транзакции:

        /**@var Transaction $transaction */
        $transaction = $balanceAccount->transaction()->create([
            'status' => PaymentTransactionStatusEnum::awaiting->value,
            'cash_account_id' => $balanceAccount->id,
            'balance' => $amount,
        ]);

        Stripe::setApiKey(config('stripe.stripe_sk'));

        $checkoutSession = Session::create([
            'metadata' => [
                'transaction_id' => $transaction->id, // вот здесь привязываем id подписки
                'account_id' => $balanceAccount->id,
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
            'amount' => $amount,
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
        $transactionId = $metadata['transaction_id'] ?? null;
        $accountId = $metadata['account_id'] ?? null;
        $amount = $metadata['amount'] ?? 0;

        if (is_null($transactionId)) {
            Log::error('Account ID not found in metadata', [
                'available_metadata' => $metadata,
            ]);
            return response()->json(['account_id is null'], 400);
        }

        $account = CashAccount::query()->find($accountId);
        $transaction = Transaction::query()->find($transactionId);

        if (
            $event->type === "checkout.session.completed" ||
            $event->type === "checkout.session.async_payment_succeeded"
        ) {
            //логика при успешном зачислении
            $transaction->status = PaymentTransactionStatusEnum::adding->value;
            $account->status = PaymentAccountStatusEnum::active->value;
            $account->total_balance += $amount;
        }
        //логика при не успешном зачислении
        else if (
            $event->type === "checkout.session.expired"
        ) {
            $transaction->status = PaymentTransactionStatusEnum::expired->value;
        } else if (
            $event->type === "checkout.session.async_payment_failed"
        ) {
            $transaction->status = PaymentTransactionStatusEnum::failed->value;
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
        return response()->json(['message' => 'совершена оплата'], 200);
    }

    public function cancel()
    {
        return response()->json(['message' => 'оплата отменена'], 200);
    }
}
