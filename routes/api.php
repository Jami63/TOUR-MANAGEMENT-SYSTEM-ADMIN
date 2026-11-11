<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\MemberSummaryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TourBackpackController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\TourMemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile REST API Routes – SafarFly
|--------------------------------------------------------------------------
*/

// Public Authentication with Email OTP Verification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Mobile Routes
Route::middleware('auth:sanctum')->group(function () {
    // Current User Profile & Logout
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile/photo', [AuthController::class, 'updateProfilePhoto']);
    Route::delete('/profile/photo', [AuthController::class, 'clearProfilePhoto']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tours API
    Route::get('/tours', [TourController::class, 'index']);
    Route::post('/tours', [TourController::class, 'store']);
    Route::get('/tours/{id}', [TourController::class, 'show']);
    Route::put('/tours/{id}', [TourController::class, 'update']);
    Route::delete('/tours/{id}', [TourController::class, 'destroy']);
    Route::get('/tours/{id}/settlement-preview', [TourController::class, 'settlementPreview']);
    Route::post('/tours/{id}/finish', [TourController::class, 'finish']);
    Route::post('/tours/{id}/emergency-finish', [TourController::class, 'emergencyFinish']);
    Route::get('/tours/{id}/refunds', [TourController::class, 'refunds']);
    Route::post('/refunds/{refundId}/status', [TourController::class, 'updateRefundStatus']);

    // Tour Participants & Members API (Screens 12 & 34)
    Route::get('/tours/{tourId}/members', [TourMemberController::class, 'index']);
    Route::post('/tours/{tourId}/members', [TourMemberController::class, 'store']);
    Route::delete('/tours/{tourId}/members/{memberId}', [TourMemberController::class, 'destroy']);

    // Tour Backpack Checklist API (Category-wise dynamic checklist)
    Route::get('/tours/{tourId}/backpack-items', [TourBackpackController::class, 'index']);
    Route::post('/tours/{tourId}/backpack-items', [TourBackpackController::class, 'store']);
    Route::patch('/tours/{tourId}/backpack-items/{itemId}/toggle', [TourBackpackController::class, 'toggle']);
    Route::delete('/tours/{tourId}/backpack-items/{itemId}', [TourBackpackController::class, 'destroy']);
    Route::post('/tours/{tourId}/backpack-items/reset', [TourBackpackController::class, 'resetCategory']);
    Route::get('/users/search', [TourMemberController::class, 'searchUsers']);

    // Expenses & Auto-Split API
    Route::get('/tours/{tourId}/expenses', [ExpenseController::class, 'index']);
    Route::post('/tours/{tourId}/expenses', [ExpenseController::class, 'store']);

    // Manual Payment API (Tour Manager logs payments directly per expense or advance - Requirement 4)
    Route::post('/tours/{tourId}/payments', [PaymentController::class, 'recordTourPayment']);
    Route::get('/tours/{tourId}/payments', [PaymentController::class, 'getTourPayments']);
    Route::put('/tours/{tourId}/payments/{paymentId}', [PaymentController::class, 'updateTourPayment']);
    Route::post('/tours/{tourId}/payments/{paymentId}/update', [PaymentController::class, 'updateTourPayment']);
    Route::delete('/tours/{tourId}/payments/{paymentId}', [PaymentController::class, 'deleteTourPayment']);
    Route::post('/tours/{tourId}/expenses/{expenseId}/payment', [PaymentController::class, 'recordManualPayment']);

    // Payments API (Screens 23, 37, 38 & 39)
    Route::get('/payments/pending', [PaymentController::class, 'pending']);
    Route::get('/payments/history', [PaymentController::class, 'history']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::post('/payments/submit', [PaymentController::class, 'submit']);
    Route::post('/payments/{id}/verify', [PaymentController::class, 'verify']);

    // Member Personal Expense & Dues Summary
    Route::get('/tours/{tourId}/my-summary', [MemberSummaryController::class, 'myTourSummary']);
});
