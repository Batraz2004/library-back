<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        /**@var User $user */
        $user = Auth::user();

        $query = $user->cart->cartItems()->getQuery();
        $checkedCartItems = $query->isActive()->isChecked()->get();

        
    }
}
