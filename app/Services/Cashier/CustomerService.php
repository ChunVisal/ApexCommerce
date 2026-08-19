<?php

namespace App\Services\Cashier;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerService
{
    public static function getSummaryCards()
    {
        $userId = Auth::id();

        $customers = Customer::whereHas('orders', fn($q) => $q->where('cashier_id', $userId)
            ->where('status', '!=', 'refunded'))
            ->withCount(['orders as total_orders' => fn($q) => $q->where('cashier_id', $userId)
                ->where('status', '!=', 'refunded')])
            ->withSum(['orders as total_spent' => fn($q) => $q->where('cashier_id', $userId)
                ->where('status', '!=', 'refunded')], 'total')
            ->get();

        return [
            [
                'title' => 'Total Customers',
                'value' => $customers->count(),
                'icon' => 'fa-solid fa-users',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'subtitle' => 'Your customers',
            ],
            [
                'title' => 'VIP Members',
                'value' => $customers->filter(fn($c) => $c->total_orders >= 6 || $c->total_spent >= 5000)->count(),
                'icon' => 'fa-solid fa-crown',
                'iconBg' => '#EAB308',
                'iconColor' => '#EAB308',
                'badge' => '5% OFF',
                'subtitle' => 'Spent over $5,000',
            ],
            [
                'title' => 'Regular',
                'value' => $customers->filter(fn($c) => ($c->total_orders >= 3 || $c->total_spent >= 2000) && $c->total_orders < 6 && $c->total_spent < 5000)->count(),
                'icon' => 'fa-solid fa-repeat',
                'iconBg' => '#2563EB',
                'iconColor' => '#2563EB',
                'subtitle' => '3+ orders',
            ],
            [
                'title' => 'New Customers',
                'value' => $customers->filter(fn($c) => $c->total_orders < 3 && $c->total_spent < 2000)->count(),
                'icon' => 'fa-solid fa-walking',
                'iconBg' => '#16A34A',
                'iconColor' => '#16A34A',
                'subtitle' => '1-2 orders',
            ],

        ];
    }

    public static function determineSegment($orders, $spent)
    {
        if ($orders >= 6 || $spent >= 5000) {
            return 'vip';
        } elseif ($orders >= 3 || $spent >= 2000) {
            return 'regular';
        }
        return 'new';
    }

    public static function getCustomersWithSegments()
    {
        $cashierId = Auth::id();

        return Customer::whereHas('orders', function ($q) use ($cashierId) {
            $q->where('cashier_id', $cashierId)
                ->where('status', '!=', 'refunded');
        })
            ->withCount(['orders as total_orders' => function ($q) use ($cashierId) {
                $q->where('cashier_id', $cashierId)
                    ->where('status', '!=', 'refunded');
            }])
            ->withSum(['orders as total_spent' => function ($q) use ($cashierId) {
                $q->where('cashier_id', $cashierId)
                    ->where('status', '!=', 'refunded');
            }], 'total')
            ->orderBy('last_order_at', 'desc')
            ->get()
            ->map(function ($customer) {
                $customer->segment = self::determineSegment($customer->total_orders ?? 0, $customer->total_spent ?? 0);
                return $customer;
            });
    }
}
