<?php

namespace App\Traits\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait UserAuthTest
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
