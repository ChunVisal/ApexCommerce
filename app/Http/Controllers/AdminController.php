<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // public function dashboard(Request $request)
    // {
    //    return
    // }

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
