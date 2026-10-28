<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountTransactionController;

Route::middleware('api.key')->group(function () {
    Route::get('/account-transactions', [AccountTransactionController::class, 'index']);
    Route::get('/account-transactions/unmatched-count', [AccountTransactionController::class, 'getCountUnmatchedRecords']);
    Route::get('/account-transactions/{id}', [AccountTransactionController::class, 'show']);
    Route::patch('/account-transactions/{id}', [AccountTransactionController::class, 'update']);
    Route::patch('/account-transactions/batch-update', [AccountTransactionController::class, 'batchUpdateStatus']);
});