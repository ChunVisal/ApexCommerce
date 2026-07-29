<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityData
{

    public static function log($action, $description, $page, $status = 'info', $metadata = null)
    {
        $user = Auth::id();
        if (!$user) return;

        ActivityLog::create([
            'user_id' => $user,
            'user_name' => Auth::user() ? Auth::user()->name : null,
            'action' => $action,
            'description' => $description,
            'page' => $page,
            'status' => $status,
            'metadata' => $metadata,
        ]);
    }

    public static function getSummary()
    {
        return [
            [
                'title' => 'Total Activities',
                'value' => '2,847',
                'icon' => 'fa-solid fa-bolt',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'trend' => 'up',
                'percentage' => '8.3%',
                'period' => 'vs last 7 days',
            ],
            [
                'title' => 'Audit Records',
                'value' => '1,234',
                'icon' => 'fa-solid fa-clipboard-list',
                'iconBg' => '#8B5CF6',
                'iconColor' => '#8B5CF6',
                'trend' => 'up',
                'percentage' => '4.7%',
                'period' => 'vs last 7 days',
            ],
            [
                'title' => 'Changes Today',
                'value' => '67',
                'icon' => 'fa-solid fa-code-compare',
                'iconBg' => '#F59E0B',
                'iconColor' => '#F59E0B',
                'trend' => 'up',
                'percentage' => '12.1%',
                'period' => 'vs yesterday',
            ],
            [
                'title' => 'Active Sessions',
                'value' => '12',
                'icon' => 'fa-solid fa-user-circle',
                'iconBg' => '#10B981',
                'iconColor' => '#10B981',
                'trend' => 'down',
                'percentage' => '3.2%',
                'period' => 'vs last 7 days',
            ],
        ];
    }

    public static function getActivities()
    {
        $users = [
            ['name' => 'Sokha Chan', 'color' => '#0F6E8C'],
            ['name' => 'Dara Kim', 'color' => '#8B5CF6'],
            ['name' => 'Maly Touch', 'color' => '#F59E0B'],
            ['name' => 'Vicheka Sam', 'color' => '#10B981'],
        ];

        $actions = ['created', 'updated', 'deleted', 'logged in', 'logged out', 'exported', 'viewed', 'generated'];
        $modules = ['Products', 'Orders', 'Users', 'Customers', 'Reports', 'Settings', 'Payments'];
        $entities = ['Product #101', 'Order #2034', 'User #45', 'Customer #67', 'Report', 'Configuration'];
        $statuses = ['success', 'warning', 'error'];

        $activities = [];
        $dates = collect(range(0, 6))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));

        foreach ($dates as $dateIndex => $date) {
            $dayActivities = rand(3, 8);
            for ($i = 0; $i < $dayActivities; $i++) {
                $user = $users[array_rand($users)];
                $action = $actions[array_rand($actions)];
                $module = $modules[array_rand($modules)];

                $activities[] = [
                    'date' => $date,
                    'time' => now()->subDays($dateIndex)->subHours(rand(1, 23))->format('g:i A'),
                    'user' => $user['name'],
                    'initials' => collect(explode(' ', $user['name']))->map(fn($n) => $n[0])->take(2)->implode(''),
                    'color' => $user['color'],
                    'action' => $action,
                    'module' => $module,
                    'entity' => rand(0, 1) ? $entities[array_rand($entities)] : null,
                    'details' => rand(0, 1) ? 'Additional context about the action' : null,
                    'status' => $statuses[array_rand($statuses)],
                ];
            }
        }

        return collect($activities)->sortByDesc('date')->values();
    }

    public static function getAuditLogs()
    {
        $users = ['Sokha Chan', 'Dara Kim', 'Maly Touch', 'Vicheka Sam'];
        $actionTypes = ['create', 'update', 'delete', 'login', 'logout', 'export'];
        $modules = ['Products', 'Orders', 'Users', 'Customers', 'Reports', 'Settings'];
        $statuses = ['success', 'failed'];
        $ips = ['192.168.1.1', '10.0.0.5', '203.0.113.42', '198.51.100.7'];

        $logs = [];
        for ($i = 0; $i < 15; $i++) {
            $user = $users[array_rand($users)];
            $actionType = $actionTypes[array_rand($actionTypes)];
            $module = $modules[array_rand($modules)];

            $logs[] = [
                'timestamp' => now()->subHours(rand(1, 72))->format('Y-m-d H:i:s'),
                'user' => $user,
                'initials' => collect(explode(' ', $user))->map(fn($n) => $n[0])->take(2)->implode(''),
                'action_type' => $actionType,
                'target' => $module . ' #' . rand(100, 999),
                'module' => $module,
                'ip' => $ips[array_rand($ips)],
                'status' => $statuses[array_rand($statuses)],
                'changes' => rand(0, 1) ? ['field' => 'price', 'old' => rand(10, 100), 'new' => rand(10, 100)] : null,
            ];
        }

        return collect($logs)->sortByDesc('timestamp')->values();
    }

    public static function getModules()
    {
        return ['Products', 'Orders', 'Users', 'Customers', 'Reports', 'Settings', 'Payments'];
    }

    public static function getEvents()
    {
        return ['created', 'updated', 'deleted', 'login', 'logout', 'exported', 'viewed', 'generated'];
    }
}
