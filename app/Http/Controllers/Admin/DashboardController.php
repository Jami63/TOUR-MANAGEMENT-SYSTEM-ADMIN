<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Super Admin Dashboard:
     * High-level platform monitoring of all tours, finances, emergency finishes, and accounts.
     * Strictly Read-Only.
     */
    public function index(Request $request): View
    {
        $superAdmin = Auth::user();

        // 1. Account Summary Statistics
        $totalUsers = User::where('role', '!=', 'super_admin')->count();
        $totalManagers = User::where('role', 'manager')->count();
        $totalMembers = User::where('role', 'member')->count();

        // 2. Platform Tour & Financial Statistics
        $totalTours = Tour::count();
        $activeTours = Tour::whereIn('status', ['upcoming', 'ongoing'])->count();
        $completedTours = Tour::where('status', 'completed')->where('finish_type', 'normal')->count();
        $emergencyTours = Tour::where('finish_type', 'emergency')->count();

        $totalBudgetSum = Tour::sum('estimated_budget');
        $totalExpensesSum = Expense::sum('total_amount');
        $totalCollectedSum = Payment::where('status', 'verified')->sum('amount');
        $totalRefundsSum = Refund::sum('refund_amount');

        // Recent Tours for Monitoring Feed
        $recentTours = Tour::with(['creator', 'members'])->latest()->take(5)->get();

        // 3. User Accounts Table
        $query = User::where('role', '!=', 'super_admin')
            ->select(['id', 'full_name', 'email', 'phone', 'role', 'created_at']);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);

        return view('admin.dashboard', compact(
            'superAdmin',
            'totalUsers',
            'totalManagers',
            'totalMembers',
            'totalTours',
            'activeTours',
            'completedTours',
            'emergencyTours',
            'totalBudgetSum',
            'totalExpensesSum',
            'totalCollectedSum',
            'totalRefundsSum',
            'recentTours',
            'users'
        ));
    }
}
