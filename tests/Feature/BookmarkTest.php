<?php

namespace Tests\Feature;

use App\Traits\Tests\UserAuthTest;
use Database\Seeders\BookSeeder;
use Database\Seeders\RoleseAndPermissionsSeeder;
use Database\Seeders\SellerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookmarkTest extends TestCase
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

    public function test_bookmarkApi(): void
    {
        $token = $this->userAuth();

        $headers = [
            'Accept' => 'application/json'
        ];

        //добавить книгу в корзину:
        $bookmarkItemData = [
            'book_id' => 1,
            'quantity' => 1,
        ];

        //доавбление нескольких книг
        $bookmarkItem = $this->withToken($token)->withHeaders($headers)->post('/api/bookmark/add', $bookmarkItemData);
        $bookmarkItem->assertStatus(200);

        $bookmarkItemDataFirst = $bookmarkItem->json('data');
        $bookmarkItemDataFirst = $bookmarkItemDataFirst['quantity'];
        $this->assertEquals($bookmarkItemDataFirst, 1);

        $bookmarkItem = $this->withToken($token)->withHeaders($headers)->post('/api/bookmark/add', $bookmarkItemData);
        $bookmarkItem->assertStatus(200);

        $bookmarkItemDataSecond = $bookmarkItem->json('data');
        $bookmarkItemDataSecondQuantity = $bookmarkItemDataSecond['quantity'];
        $this->assertEquals($bookmarkItemDataSecondQuantity, 2);

        //добавление новой книги
        $bookmarkItemData['book_id'] = 2;

        $bookmarkItemNew = $this->withToken($token)->withHeaders($headers)->post('/api/bookmark/add', $bookmarkItemData);
        $bookmarkItemNew->assertStatus(200);

        $bookmarkItemNew = $bookmarkItemNew->json('data');
        $bookmarkItemNewQuantity = $bookmarkItemNew['quantity'];
        $this->assertEquals($bookmarkItemNewQuantity, 1);

        //добавление с конкретным количеством
        $bookmarkItemData['book_id'] = 3;
        $bookmarkItemData['quantity'] = 3;
        $bookmarkItemNew = $this->withToken($token)->withHeaders($headers)->post('/api/bookmark/add', $bookmarkItemData);
        $bookmarkItemNew->assertStatus(200);

        $bookmarkItemNew = $bookmarkItemNew->json('data');
        $bookmarkItemNewQuantity = $bookmarkItemNew['quantity'];
        $this->assertEquals($bookmarkItemNewQuantity, 3);

        //проверим список коризны
        $bookmarkLisData = $this->withToken($token)->withHeaders($headers)->get('/api/bookmark/list');
        $bookmarkLisData->assertStatus(200);

        $bookmarkLisDataData = $bookmarkLisData->json('data');
        $this->assertEquals(count($bookmarkLisDataData), 3);

        //удалим элемент корзины
        $bookmarkItemDeleteItem = $this->withToken($token)->withHeaders($headers)->delete('api/bookmark/delete/' . $bookmarkLisDataData[0]['id']);
        $bookmarkItemDeleteItem->assertStatus(200);

        //еще раз получим элементы корзины
        $bookmarkLisData = $this->withToken($token)->withHeaders($headers)->get('/api/bookmark/list');
        $bookmarkLisData->assertStatus(200);

        $bookmarkLisDataData = $bookmarkLisData->json('data');
        $this->assertEquals(count($bookmarkLisDataData), 2);

        //полностью очистим корзину
        $bookmarkDelete = $this->withToken($token)->withHeaders($headers)->delete('api/bookmark/delete');
        $bookmarkDelete->assertStatus(200);

        //еще раз получим элементы корзины
        $bookmarkLisDataNew = $this->withToken($token)->withHeaders($headers)->get('/api/bookmark/list');
        $bookmarkLisDataNew->assertStatus(200);

        $bookmarkLisDataData = $bookmarkLisDataNew->json('data');
        $this->assertEquals(count($bookmarkLisDataData), 0);
    }
}
