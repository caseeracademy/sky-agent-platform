<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agent\PayoutReceiptController;

Route::get('/', function () {
    return view('landing');
});

// Agent payout receipt downloads (protected by auth middleware)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAgent::class])->group(function () {
    Route::get('/agent/payout/{payout}/receipt', [PayoutReceiptController::class, 'download'])
        ->name('agent.payout.receipt');
});
