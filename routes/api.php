<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BankMutationController;

Route::middleware('api.key')->group(function () {
    Route::get('/bank-mutations', [BankMutationController::class, 'index']);
    Route::get('/bank-mutations/unmatched-count', [BankMutationController::class, 'getCountUnmatchedRecords']);
    Route::get('/bank-mutations/{id}', [BankMutationController::class, 'show']);
    
    // Route::patch('/bank-mutations/{id}/status', [BankMutationController::class, 'updateStatus']);
});