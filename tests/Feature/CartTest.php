<?php

namespace Tests\Feature;

use App\Traits\Tests\UserAuthTest;
use Database\Seeders\BookSeeder;
use Database\Seeders\RoleseAndPermissionsSeeder;
use Database\Seeders\SellerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase; //очистка тестовой бд
    use UserAuthTest;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleseAndPermissionsSeeder::class);
        $this->seed(SellerSeeder::class);
        $this->seed(BookSeeder::class);
    }

    public function test_cartApi(): void
    {
        $token = $this->userAuth();

        //добавить книгу в корзину:
        $cartAddItemData = [
            'book_id' => 1,
            'quantity' => 1,
        ];

        //доавбление нескольких книг
        $cartAddItem = $this->withToken($token)->post('/api/cart/add', $cartAddItemData);
        $cartAddItem->assertStatus(200);

        $cartItemDataFirst = $cartAddItem->json('data');
        $cartItemDataFirstQuantity = $cartItemDataFirst['quantity'];
        $this->assertEquals($cartItemDataFirstQuantity, 1);

        $cartAddItem = $this->withToken($token)->post('/api/cart/add', $cartAddItemData);
        $cartAddItem->assertStatus(200);

        $cartItemDataSecond = $cartAddItem->json('data');
        $cartItemDataSecondQuantity = $cartItemDataSecond['quantity'];
        $this->assertEquals($cartItemDataSecondQuantity, 2);

        //добавление новой книги
        $cartAddItemData['book_id'] = 2;

        $cartAddItemNew = $this->withToken($token)->post('/api/cart/add', $cartAddItemData);
        $cartAddItemNew->assertStatus(200);

        $carAddItemNew = $cartAddItemNew->json('data');
        $carAddItemNewQuantity = $carAddItemNew['quantity'];
        $this->assertEquals($carAddItemNewQuantity, 1);

        //добавление с конкретным количеством
        $cartAddItemData['book_id'] = 3;
        $cartAddItemData['quantity'] = 3;
        $cartAddItemNew = $this->withToken($token)->post('/api/cart/add', $cartAddItemData);
        $cartAddItemNew->assertStatus(200);

        $cartAddItemNew = $cartAddItemNew->json('data');
        $cartAddItemNewQuantity = $cartAddItemNew['quantity'];
        $this->assertEquals($cartAddItemNewQuantity, 3);

        //проверим список коризны
        $cartList = $this->withToken($token)->get('/api/cart/list');
        $cartList->assertStatus(200);

        $cartListData = $cartList->json('data');
        $this->assertEquals(count($cartListData), 3);

        //удалим элемент корзины
        $cartDeleteItem = $this->withToken($token)->delete('api/cart/delete/' . $cartListData[0]['id']);
        $cartDeleteItem->assertStatus(200);

        //еще раз получим элементы корзины
        $cartList = $this->withToken($token)->get('/api/cart/list');
        $cartList->assertStatus(200);

        $cartListData = $cartList->json('data');
        $this->assertEquals(count($cartListData), 2);

        //полностью очистим корзину
        $cartDelete = $this->withToken($token)->delete('api/cart/delete');
        $cartDelete->assertStatus(200);

        //еще раз получим элементы корзины
        $cartListNew = $this->withToken($token)->get('/api/cart/list');
        $cartListNew->assertStatus(200);

        $cartListData = $cartListNew->json('data');
        $this->assertEquals(count($cartListData), 0);
    }

}
