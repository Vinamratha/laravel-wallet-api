<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:1'
        ]);

        $sender = Auth::user();
        $receiver = User::where('email', $request->email)->first();

        if (!$receiver) {
            return response()->json(['message' => 'Receiver not found'], 404);
        }

        if ($request->email == auth()->user()->email) {
            return response()->json(['message' => 'Cannot transfer to yourself'], 400);
        }

        $senderWallet = Wallet::where('user_id', $sender->id)->first();
        $receiverWallet = Wallet::where('user_id', $receiver->id)->first();

        if ($senderWallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::beginTransaction();

        try {
            // Deduct from sender
            $senderWallet->balance -= $request->amount;
            $senderWallet->save();

            // Add to receiver
            $receiverWallet->balance += $request->amount;
            $receiverWallet->save();

            // Record transaction
            Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $request->amount,
                'type' => 'transfer'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer completed'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Transaction failed'
            ], 500);
        }
    }

    public function history()
    {
        
        try{
            $user = Auth::user();

            if (!$user->id) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $transactions = Transaction::with(['sender:id,email', 'receiver:id,email'])
                ->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($transactions);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
