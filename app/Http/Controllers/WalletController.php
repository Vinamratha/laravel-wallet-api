<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;

class WalletController extends Controller
{
    public function balance()
    {
        $wallet = Wallet::where('user_id', Auth::id())->first();

        return response()->json([
            'balance' => $wallet->balance
        ]);
    }

    public function addMoney(Request $request)
    {
        $wallet = Wallet::where('user_id', Auth::id())->first();

        $wallet->balance += $request->amount;
        $wallet->save();

        return response()->json([
            'message' => 'Money added',
            'balance' => $wallet->balance
        ]);
    }
}
