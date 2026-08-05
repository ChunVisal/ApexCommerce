<?php

namespace App\Services\Admin;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerService
{
    public static function getSummaryCards()
    {
        $totalCustomers = Customer::count('*');

        // Calculate segments based on actual order data
        $customers = Customer::withCount(['orders as total_orders' => fn($q) => $q->where('status', '!=', 'refunded')])
            ->withSum(['orders as total_spent' => fn($q) => $q->where('status', '!=', 'refunded')], 'total')
            ->get();

        $vip = $customers->filter(fn($c) => $c->total_orders >= 6 || $c->total_spent >= 5000)->count();
        $regular = $customers->filter(fn($c) => ($c->total_orders >= 3 || $c->total_spent >= 2000) && $c->total_orders < 6 && $c->total_spent < 5000)->count();
        $new = $customers->filter(fn($c) => $c->total_orders < 3 && $c->total_spent < 2000)->count();

        return [
            [
                'title' => 'Total Customers',
                'value' => $totalCustomers,
                'icon' => 'fa-solid fa-users',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'subtitle' => 'Across all cashiers',
            ],
            [
                'title' => 'VIP Members',
                'value' => $vip,
                'icon' => 'fa-solid fa-crown',
                'iconBg' => '#EAB308',
                'iconColor' => '#EAB308',
                'badge' => '5% OFF',
                'subtitle' => '6+ orders or $5,000+',
            ],
            [
                'title' => 'Regular',
                'value' => $regular,
                'icon' => 'fa-solid fa-repeat',
                'iconBg' => '#2563EB',
                'iconColor' => '#2563EB',
                'subtitle' => '3-5 orders or $2,000+',
            ],
            [
                'title' => 'New Customers',
                'value' => $new,
                'icon' => 'fa-solid fa-walking',
                'iconBg' => '#16A34A',
                'iconColor' => '#16A34A',
                'subtitle' => '1-2 orders',
            ],
        ];
    }
}
