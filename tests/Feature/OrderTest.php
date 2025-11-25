<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentAccountStatusEnum;
use App\Models\CashAccount;
use App\Traits\Tests\UserAuthTest;
use Database\Seeders\BookSeeder;
use Database\Seeders\RoleseAndPermissionsSeeder;
use Database\Seeders\SellerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase; //очистка тестовой бд
    use UserAuthTest;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleseAndPermissionsSeeder::class);
        $this->seed(SellerSeeder::class);
        $this->seed(BookSeeder::class);
    }

    public function tearDown(): void
    {
        // обнулм счетчики id и очистим таблицы(refreshDatabase не обнуляет бд)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('books')->truncate();
        DB::table('sellers')->truncate();

        parent::tearDown();
    }
    public function test_example(): void
    {
        $token = $this->userAuth();

        $headers = [
            'Accept' => 'application/json'
        ];

        //добавить книгу в корзину:
        $cartAddItemData = [
            'book_id' => 1,
            'quantity' => 5,
        ];

        //доавбление нескольких книг
        $cartAddItem = $this->withToken($token)->withHeaders($headers)->post('/api/cart/add', $cartAddItemData);
        $cartAddItem->assertStatus(200);

        $cartItemDataFirst = $cartAddItem->json('data');
        $cartItemDataFirstQuantity = $cartItemDataFirst['quantity'];
        $this->assertEquals($cartItemDataFirstQuantity, 5);

        //доавбление нескольких книг
        $cartAddItemData['book_id'] = 2;
        $cartAddItemData['quantity'] = 2;
        $cartAddItem = $this->withToken($token)->withHeaders($headers)->post('/api/cart/add', $cartAddItemData);
        $cartAddItem->assertStatus(200);

        $cartItemDataFirst = $cartAddItem->json('data');
        $cartItemDataFirstQuantity = $cartItemDataFirst['quantity'];
        $this->assertEquals($cartItemDataFirstQuantity, 2);

        //проверим список
        $cartList = $this->withToken($token)->withHeaders($headers)->get('/api/cart/list');
        $cartList->assertStatus(200);

        $cartListData = $cartList->json('data');
        $this->assertEquals(count($cartListData), 2);

        //пополним баланс пользователя
        $balanceReqeustData = [
            "amount" => 12700,
            "currency" => "rub",
        ];
        $balanceCreate = $this->withToken($token)->withHeaders($headers)->post('/api/balance/add', $balanceReqeustData);
        $balanceCreate->assertStatus(200);
        $balanceResponce = $balanceCreate->json();

        //укажем статус активным чтобы можно было оформить заказ
        $cashAccount = CashAccount::query()->first();
        $cashAccount->status = PaymentAccountStatusEnum::active->value;
        $cashAccount->save();

        //проверим баланс
        $getBalanceResponse = $this->withToken($token)->withHeaders($headers)->get('/api/balance/get');
        $getBalanceResponse->assertStatus(200);
        $getBalanceResponse = $getBalanceResponse->json();

        $this->assertEquals($getBalanceResponse['data']['total_balance'], 12700);
        //оформим заказы
        $orderCreateData = [
            'address' => 'st.street 50',
            'phone' => '79999999999',
        ];

        //оформление нескольких книг
        $orderCreate = $this->withToken($token)->withHeaders($headers)->post('/api/order/add', $orderCreateData);
        $orderCreate->assertStatus(200);

        //проверим формлены ли заказы
        $orderCreateData = $orderCreate->json('data');
        $orderCreateData = $orderCreateData['items'];
        $this->assertEquals(count($orderCreateData), 2);

        //получим список наших заказов
        $orderGet = $this->withToken($token)->withHeaders($headers)->get('/api/order/list');
        $orderGet->assertStatus(200);

        //проверим формлены ли заказы
        $orderGetData = $orderGet->json('data')[0];
        $orderGetDataStatus = $orderGetData['status'];
        $orderGetDataItems = $orderGetData['items'];

        $this->assertEquals($orderGetDataStatus, OrderStatusEnum::active->value);
        $this->assertEquals(count($orderGetDataItems), 2);

        //отменим один заказ
        $orderCancel = $this->withToken($token)->withHeaders($headers)->post('/api/order/cancel/' . $orderGetData['id']);
        $orderCancel->assertStatus(200);

        //проверим отменился ли заказам
        $orderGet = $this->withToken($token)->withHeaders($headers)->get('/api/order/list');
        $orderGet->assertStatus(200);

        //проверим формлены ли заказы
        $orderGetData = $orderGet->json('data')[0];
        $orderGetDataStatus = $orderGetData['status'];
        $orderGetDataItems = $orderGetData['items'];

        $this->assertEquals($orderGetDataStatus, OrderStatusEnum::cancelled->value);
        $this->assertEquals(count($orderGetDataItems), 2);
    }
}
