<?php

namespace App\Http\Controllers\Client;

use App\Enums\RoleEnum;
use App\Events\AuthEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function createUser(RegistrationRequest $request)
    {
        $result = DB::transaction(function () use ($request) {
            $user = User::create($request->getData());
            $user->assignRole(RoleEnum::USER->value);

            if (!$user)
                throw new Exception('не удалось зарегистрировать пользователя');
            else {
                $token = $user->createToken('auth_token')->plainTextToken;
                $user->remember_token = Hash::make($token);
                $user->save();
            }

            AuthEvent::dispatch($user->id);

            return $token;
        });

        if ($result)
            return response()->json(['message' => 'пользователь успешно создан', 'token' => $result], 200);

        else
            return response()->json(['message' => 'не удалось авторизоваться'], 200);
    }
}
