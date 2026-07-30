<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $activities = ActivityLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->page, fn($q) => $q->where('page', $request->page))
            ->latest()
            ->get();

        $users = User::all();
        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        $pages = ActivityLog::select('page')->distinct()->pluck('page');

        return view('admin.activity-log', compact('activities', 'users', 'actions', 'pages'));
    }

    public function test()
    {
        return view('admin.activitylog');
    }
}
