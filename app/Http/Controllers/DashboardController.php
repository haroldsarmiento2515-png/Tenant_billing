<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $admins = User::where('role', 'admin')->count();

        return response()->json([
            'totals' => [
                'users' => $totalUsers,
                'verified_users' => $verifiedUsers,
                'unverified_users' => max($totalUsers - $verifiedUsers, 0),
                'admins' => $admins,
            ],
            'recent_signups' => User::orderByDesc('created_at')
                ->take(5)
                ->get(['id', 'name', 'email', 'role', 'created_at', 'email_verified_at']),
        ]);
    }
}
