<?php

namespace Tests\Feature;

use Database\Seeders\RoleseAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function userAuth(): string
    {
        //авторизация 
        $userData = [
            'email'                 => 'tester@gmail.com',
            'name'                  => 'TesterGuy',
            'password'              => 'TesterPasword',
            'password_confirmation' => 'TesterPasword',
        ];

        $response = $this->post('/api/registration', $userData);

        $bodyJson = $response->json();
        $token = $bodyJson['token'];

        $response->assertStatus(200);

        return $token;
    }
}
