<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use Carbon\Carbon;

class ActivityService
{
    public static function log($action, $description, $page, $status = 'info')
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => $action,
            'description' => $description,
            'page' => $page,
            'status' => $status,
        ]);
    }
    public static function getSummaryCards()
    {
        $today = ActivityLog::whereDate('created_at', Carbon::today())->count();
        $yesterday = ActivityLog::whereDate('created_at', Carbon::yesterday())->count();
        $last7Days = ActivityLog::whereDate('created_at', '>=', Carbon::now()->subDays(7))->count();
        $change = $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100) : 0;

        $mostActive = ActivityLog::selectRaw('user_name, count(*) as count')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->groupBy('user_name')
            ->orderByDesc('count')
            ->first();

        $warnings = ActivityLog::where('status', 'warning')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();

        $danger = ActivityLog::where('status', 'danger')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();
        return [
            [
                'title' => 'Today\'s Activities',
                'value' => $today,
                'icon' => 'fa-solid fa-bolt',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'trend' => abs($change) . '%',
                'trendDirection' => $change >= 0 ? 'up' : 'down',
                'trendColor' => $change >= 0 ? 'text-green-500' : 'text-red-500',
                'subtitle' => 'vs yesterday (' . $yesterday . ')',

            ],
            [
                'title' => 'Last 7 Days',
                'value' => $last7Days,
                'icon' => 'fa-solid fa-calendar-week',
                'iconBg' => '#8B5CF6',
                'iconColor' => '#8B5CF6',
                'subtitle' => 'Total logs recorded',
            ],
            [
                'title' => 'Most Active',
                'value' => $mostActive->user_name ?? 'N/A',
                'icon' => 'fa-solid fa-user-check',
                'iconBg' => '#10B981',
                'iconColor' => '#10B981',
                'subtitle' => ($mostActive->count ?? 0) . ' actions this week',
            ],
            [
                'title' => 'Danger',
                'value' => $danger,
                'icon' => 'fa-solid fa-triangle-exclamation',
                'iconBg' => $danger > 0 ? '#EF4444' : '#F59E0B',
                'iconColor' => $danger > 0 ? '#EF4444' : '#F59E0B',
                'subtitle' => 'Warnings: ' . $warnings . ' recorded past week',
            ],

        ];
    }
}
