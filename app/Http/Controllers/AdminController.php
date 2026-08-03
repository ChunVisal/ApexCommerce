<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $start = $request->start_date ?? now()->subDays(14)->format('Y-m-d');
        $end = $request->end_date ?? now()->format('Y-m-d');

        $totalRevenue = Order::where('status', '!=', 'refunded')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        return view('admin.dashboard', [
            'start' => $start,
            'end' => $end,
            'totalRevenue' => $totalRevenue,
            'summaryCards' => DashboardController::getSummaryCards(),
            'topProducts' => DashboardController::getTopProducts(),
            'topCategories' => DashboardController::getTopCategories(),
            'topCashiers' => DashboardController::getTopCashiers(),
            'salesChart' => DashboardController::getSalesChart($start, $end),
            'paymentBreakdown' => DashboardController::getPaymentBreakdown(),

        ]);
    }

    public function products()
    {
        return view('admin.products');
    }

    public function inventory()
    {
        return view('admin.inventory');
    }

    public function users()
    {
        return view('admin.users');
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function customers()
    {
        return view('admin.customers');
    }

    public function activitylog()
    {
        return view('admin.activity-log');
    }
}
