<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wallet', [WalletController::class, 'balance']);
    Route::post('/wallet/add', [WalletController::class, 'addMoney']);
    Route::post('/transfer', [TransactionController::class, 'transfer']);
    Route::get('/transactions', [TransactionController::class, 'history']);
});
