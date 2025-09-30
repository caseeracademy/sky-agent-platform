<?php

use App\Http\Controllers\Agent\PayoutReceiptController;
use Illuminate\Support\Facades\Route;

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

    // Student document upload
    Route::post('/agent/students/{student}/documents', [App\Http\Controllers\Agent\StudentDocumentController::class, 'store'])
        ->name('agent.student.documents.store');

    // Student document replace
    Route::put('/agent/students/{student}/documents/{document}/replace', [App\Http\Controllers\Agent\StudentDocumentController::class, 'replace'])
        ->name('agent.student.documents.replace');

    // Student document download
    Route::get('/agent/students/{student}/documents/{document}/download', [App\Http\Controllers\Agent\StudentDocumentController::class, 'download'])
        ->name('agent.student.documents.download');
});
