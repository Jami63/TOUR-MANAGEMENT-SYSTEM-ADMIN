<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TourMonitoringController as AdminTourController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Manager\TourController as ManagerTourController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SafarFly Tour Management
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isSuperAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('manager.dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/register/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('register.otp');
Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp'])->name('register.otp.verify');
Route::post('/register/resend-otp', [AuthController::class, 'resendOtp'])->name('register.otp.resend');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Super Admin Protected Panel (Strictly Read-Only Monitoring)
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/tours', [AdminTourController::class, 'index'])->name('tours.index');
    Route::get('/tours/{id}', [AdminTourController::class, 'show'])->name('tours.show');
    Route::get('/financials/expenses', [AdminTourController::class, 'allExpenses'])->name('expenses.index');
    Route::get('/financials/payments', [AdminTourController::class, 'allPayments'])->name('payments.index');
    Route::get('/financials/refunds', [AdminTourController::class, 'allRefunds'])->name('refunds.index');
});

// Tour Manager Protected Panel (Full Tour Management)
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerTourController::class, 'index'])->name('dashboard');
    Route::get('/tours/create', [ManagerTourController::class, 'create'])->name('tours.create');
    Route::post('/tours', [ManagerTourController::class, 'store'])->name('tours.store');
    Route::get('/tours/{id}', [ManagerTourController::class, 'show'])->name('tours.show');

    // Members Management
    Route::post('/tours/{id}/members', [ManagerTourController::class, 'addMember'])->name('tours.members.add');
    Route::delete('/tours/{tourId}/members/{memberId}', [ManagerTourController::class, 'removeMember'])->name('tours.members.remove');

    // Expense Management
    Route::post('/tours/{id}/expenses', [ManagerTourController::class, 'addExpense'])->name('tours.expenses.add');

    // Manual Payment Management
    Route::post('/tours/{id}/payments', [ManagerTourController::class, 'recordAdvancePayment'])->name('tours.payments.advance');
    Route::post('/tours/{tourId}/expenses/{expenseId}/payment', [ManagerTourController::class, 'recordPayment'])->name('tours.payments.record');

    // Finish Tour & Emergency Finish Tour
    Route::post('/tours/{id}/finish', [ManagerTourController::class, 'finishTour'])->name('tours.finish');
    Route::post('/tours/{id}/emergency-finish', [ManagerTourController::class, 'emergencyFinishTour'])->name('tours.emergency-finish');

    // Refund Management
    Route::post('/refunds/{refundId}/status', [ManagerTourController::class, 'updateRefundStatus'])->name('refunds.update-status');
});
