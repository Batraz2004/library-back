<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $tokenName = $request->header('user-agent');

        $user = User::query()->firstWhere('email', $request->email);

        if (blank($user) || !Hash::check($request->password, $user->password)) { //хэш  потмоу что user -> password закэширован
            return response()->json(['message' => 'не верный логин или пароль'], 200);
        }

        $result = DB::transaction(function () use ($user, $tokenName) {
            //старые токены удалим
            $query = $user->tokens();
            $query->where('name', $tokenName);
            $query->delete();

            $token = $user->createToken($tokenName)->plainTextToken;

            $user->remember_token = Hash::make($token);
            $user->save();

            return $token;
        });

        if ($result)
            return response()->json(['message' => 'пользователь авторизован', 'token' => $result], 200);
        else
            return response()->json(['message' => 'не удалост авторизоваться', 'token' => $result], 200);
    }

    public function logout(Request $request)
    {
        //способ для сессии (тогда надо будет убрать мидлвар санктум с роута logout)
        // Auth::logout();

        // $request->session()->invalidate();

        // $request->session()->regenerateToken();

        //способ для апи:

        $request->user()->currentAccessToken()->delete();

        return response()->json(['code' => 200, 'mess' => 'выход из аккаунта'], 200);
    }
}
