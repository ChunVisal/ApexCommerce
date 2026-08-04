<?php

namespace App\Helpers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class UserService
{
    public static function getSummaryCards()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $admins = User::where('role', 'admin')->count();
        $cashiers = User::where('role', 'cashier')->count();
        $onlineNow = User::get()->filter(fn($u) => Cache::has('user-online-' . $u->id))->count();
        // Today vs yesterday
        $todayNew = User::whereDate('created_at', today())->count();
        $yesterdayNew = User::whereDate('created_at', Carbon::yesterday())->count();
        $userChange = $yesterdayNew > 0 ? round((($todayNew - $yesterdayNew) / $yesterdayNew) * 100) : ($todayNew > 0 ? 100 : 0);

        return [
            [
                'title' => 'Total Users',
                'value' => $totalUsers,
                'icon' => 'fa-solid fa-users',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'trend' => $todayNew > 0 ? 'up' : ($yesterdayNew > 0 ? 'down' : ''),
                'percentage' => $todayNew > 0 ? '+' . $todayNew : '',
                'period' => $admins . ' admin · ' . $cashiers . ' cashier',
            ],
            [
                'title' => 'Online Now',
                'value' => $onlineNow,
                'icon' => 'fa-solid fa-wifi',
                'iconBg' => '#10B981',
                'iconColor' => '#10B981',
                'trend' => $onlineNow > 0 ? 'up' : 'down',
                'percentage' => round(($onlineNow / max($activeUsers, 1)) * 100) . '%',
                'period' => 'of ' . $activeUsers . ' active',
            ],
            [
                'title' => 'Admins',
                'value' => $admins,
                'icon' => 'fa-solid fa-user-shield',
                'iconBg' => '#8B5CF6',
                'iconColor' => '#8B5CF6',
                'trend' => 'up',
                'percentage' => round(($admins / max($totalUsers, 1)) * 100) . '%',
                'period' => 'of total',
            ],
            [
                'title' => 'Cashiers',
                'value' => $cashiers,
                'icon' => 'fa-solid fa-cash-register',
                'iconBg' => '#F59E0B',
                'iconColor' => '#D97706',
                'trend' => 'up',
                'percentage' => round(($cashiers / max($totalUsers, 1)) * 100) . '%',
                'period' => 'of total',
            ],
        ];
    }

    public static function getUsers()
    {
        return User::orderByRaw("FIELD(role, 'admin', 'cashier')")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                $user->last_login = $user->last_login ? Carbon::parse($user->last_login) : null;

                return $user;
            });
    }
}
