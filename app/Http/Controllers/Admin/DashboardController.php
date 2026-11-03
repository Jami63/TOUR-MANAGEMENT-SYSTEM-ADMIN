<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Super Admin Dashboard:
     * ONLY shows who is creating accounts on the platform, whether they are a Tour Manager or a Tour Member.
     * No tours, expenses, payments, or credentials are shown or managed here.
     */
    public function index(Request $request): View
    {
        $superAdmin = Auth::user();

        // 1. Account Summary Statistics
        $totalUsers = User::where('role', '!=', 'super_admin')->count();
        $totalManagers = User::where('role', 'manager')->count();
        $totalMembers = User::where('role', 'member')->count();

        // 2. Query who is creating accounts
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

        $users = $query->latest()->paginate(15);

        return view('admin.dashboard', compact(
            'superAdmin',
            'totalUsers',
            'totalManagers',
            'totalMembers',
            'users'
        ));
    }
}
