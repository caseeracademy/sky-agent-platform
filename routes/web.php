<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agent\PayoutReceiptController;

Route::get('/', function () {
    // Check if user is authenticated
    if (auth()->check()) {
        $user = auth()->user();
        
        // Redirect based on user role
        if (in_array($user->role, ['super_admin', 'admin_staff'])) {
            return redirect('/admin');
        } elseif (in_array($user->role, ['agent_owner', 'agent_staff'])) {
            return redirect('/agent');
        }
    }
    
    return view('landing');
});

// Agent payout receipt downloads (protected by auth middleware)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAgent::class])->group(function () {
    Route::get('/agent/payout/{payout}/receipt', [PayoutReceiptController::class, 'download'])
        ->name('agent.payout.receipt');
});
