<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $tokenName = $request->header('user-agent');

        $user = User::query()->firstWhere('email', $request->email);

        if (blank($user) || !Hash::check($request->password, $user->password)) {//хэш  потмоу что user -> password закэширован
            return response()->json(['message' => 'не верный логин или пароль'], 200);
        }

        $result = DB::transaction(function() use($user, $tokenName){
            //старые токены удалим
            $query = $user->tokens();
            $query->where('name', $tokenName);
            $query->delete();
            
            $token = $user->createToken($tokenName)->plainTextToken;
            
            $user->remember_token = Hash::make($token);
            $user->save();

            return $token;
        });

        if($result)
            return response()->json(['message' => 'пользователь авторизован', 'token' => $result], 200);
        else
            return response()->json(['message' => 'не удалост авторизоваться', 'token' => $result], 200);

    }

    // public function login(Request $request)
    // {
    //     $tokenName = $request->header('user-agent');
    //     if (filled($request->email) && filled($request->password)) {
            
    //         $validateData = $request->validate([
    //             'email'    => ['required', 'max:255',],
    //             'password' => ['required', 'max:255'],
    //         ]);

    //         $user = User::query()->firstWhere('email', $request->email);

    //         if (blank($user) || !Hash::check($request->password, $user->password)) {
    //             throw ValidationException::withMessages(['pass' => 'Неверный логин или пароль.']);
    //         }

    //         //старые токены удалим
    //         $query = $user->tokens();
    //         $query->where('name', $tokenName);
    //         $query->delete();

    //         $token = $user->createToken($tokenName)->plainTextToken;
            
    //         $user->remember_token = Hash::make($token);
    //         $user->save();
    //     }
    //     else
    //     {
    //         return response()->json(['message' => 'пользователь уже существует'], 200);
    //     }

    //     return response()->json(['message' => 'пользователь успешно создан', 'token' => $token], 200);
    // }
}
