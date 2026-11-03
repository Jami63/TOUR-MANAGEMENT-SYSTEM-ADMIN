<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SafarFlow Super Admin
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Super Admin Protected Panel
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // The Super Admin Dashboard: ONLY sees who is creating accounts (Tour Manager or Member)
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});
