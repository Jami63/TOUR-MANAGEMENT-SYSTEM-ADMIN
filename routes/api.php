<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\MemberSummaryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TourController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile REST API Routes – SafarFlow
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Mobile Routes
Route::middleware('auth:sanctum')->group(function () {
    // Current User Profile & Logout
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tours API
    Route::get('/tours', [TourController::class, 'index']);
    Route::post('/tours', [TourController::class, 'store']);
    Route::get('/tours/{id}', [TourController::class, 'show']);

    // Expenses & Auto-Split API
    Route::get('/tours/{tourId}/expenses', [ExpenseController::class, 'index']);
    Route::post('/tours/{tourId}/expenses', [ExpenseController::class, 'store']);

    // Payments API
    Route::post('/payments/submit', [PaymentController::class, 'submit']);
    Route::post('/payments/{id}/verify', [PaymentController::class, 'verify']);

    // Member Personal Expense & Dues Summary
    Route::get('/tours/{tourId}/my-summary', [MemberSummaryController::class, 'myTourSummary']);
});
