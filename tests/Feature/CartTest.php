<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Bookmark;
use App\Traits\Tests\UserAuthTest;
use Database\Seeders\BookSeeder;
use Database\Seeders\RoleseAndPermissionsSeeder;
use Database\Seeders\SellerSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function tearDown(): void
    {
        // обнулм счетчики id и очистим таблицы(refreshDatabase не обнуляет бд)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('books')->truncate();
        DB::table('sellers')->truncate();

        parent::tearDown();
    }

    public function test_cartApi(): void
    {
        $token = $this->userAuth();

        $headers = [
            'Accept' => 'application/json'
        ];

        //добавить книгу в корзину:
        $cartAddItemData = [
            'book_id' => 1,
            'quantity' => 1,
        ];

        //доавбление нескольких книг
        $cartAddItem = $this->withToken($token)->withHeaders($headers)->post('/api/cart/add', $cartAddItemData);
        $cartAddItem->assertStatus(200);

        $cartItemDataFirst = $cartAddItem->json('data');
        $cartItemDataFirstQuantity = $cartItemDataFirst['quantity'];
        $this->assertEquals($cartItemDataFirstQuantity, 1);

        $cartAddItem = $this->withToken($token)->withHeaders($headers)->post('/api/cart/add', $cartAddItemData);
        $cartAddItem->assertStatus(200);

        $cartItemDataSecond = $cartAddItem->json('data');
        $cartItemDataSecondQuantity = $cartItemDataSecond['quantity'];
        $this->assertEquals($cartItemDataSecondQuantity, 2);

        //добавление новой книги
        $cartAddItemData['book_id'] = 2;

        $cartAddItemNew = $this->withToken($token)->withHeaders($headers)->post('/api/cart/add', $cartAddItemData);
        $cartAddItemNew->assertStatus(200);

        $carAddItemNew = $cartAddItemNew->json('data');
        $carAddItemNewQuantity = $carAddItemNew['quantity'];
        $this->assertEquals($carAddItemNewQuantity, 1);

        //добавление с конкретным количеством
        $cartAddItemData['book_id'] = 3;
        $cartAddItemData['quantity'] = 3;
        $cartAddItemNew = $this->withToken($token)->withHeaders($headers)->post('/api/cart/add', $cartAddItemData);
        $cartAddItemNew->assertStatus(200);

        $cartAddItemNew = $cartAddItemNew->json('data');
        $cartAddItemNewQuantity = $cartAddItemNew['quantity'];
        $this->assertEquals($cartAddItemNewQuantity, 3);

        //проверим список коризны
        $cartList = $this->withToken($token)->withHeaders($headers)->get('/api/cart/list');
        $cartList->assertStatus(200);

        $cartListData = $cartList->json('data');
        $this->assertEquals(count($cartListData), 3);

        //удалим элемент корзины
        $cartDeleteItem = $this->withToken($token)->withHeaders($headers)->delete('api/cart/delete/' . $cartListData[0]['id']);
        $cartDeleteItem->assertStatus(200);

        //еще раз получим элементы корзины
        $cartList = $this->withToken($token)->withHeaders($headers)->get('/api/cart/list');
        $cartList->assertStatus(200);

        $cartListData = $cartList->json('data');
        $this->assertEquals(count($cartListData), 2);

        //полностью очистим корзину
        $cartDelete = $this->withToken($token)->withHeaders($headers)->delete('api/cart/delete');
        $cartDelete->assertStatus(200);

        //еще раз получим элементы корзины
        $cartListNew = $this->withToken($token)->withHeaders($headers)->get('/api/cart/list');
        $cartListNew->assertStatus(200);

        $cartListData = $cartListNew->json('data');
        $this->assertEquals(count($cartListData), 0);
    }
}
