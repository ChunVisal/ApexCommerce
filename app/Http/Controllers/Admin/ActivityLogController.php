<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Admin\ActivityService;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $activities = ActivityLog::with('user')
            // search 
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))

            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->page, fn($q) => $q->where('page', $request->page))
            ->latest()
            ->get();

        $users = User::all();
        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        $status = ActivityLog::select('status')->distinct()->pluck('status');
        $pages = ActivityLog::select('page')->distinct()->pluck('page');
        $summaryCards = ActivityService::getSummaryCards();

        return view('admin.activitys.index', compact('activities', 'summaryCards', 'users', 'actions', 'pages', 'status'));
    }

    public function clear(Request $request)
    {
        if ($request->days) {
            // Delete older than X days
            ActivityLog::where('created_at', '<', now()->subDays($request->days))->delete();
            $msg = "Logs older than {$request->days} days cleared";
        } else {
            // Delete all
            ActivityLog::truncate();
            $msg = "All logs cleared";
        }

        ActivityService::log('logs_cleared', $msg, 'Activity Log', 'warning');

        return back()->with('success', $msg);
    }

    public function export(Request $request)
    {
        $activities = ActivityLog::with('user')
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->page, fn($q) => $q->where('page', $request->page))
            ->latest()
            ->get();

        $filename = 'activity_log_' . now()->format('Y_m_d') . '.csv';

        return response()->streamDownload(function () use ($activities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'User', 'Action', 'Description', 'Page', 'Status']);
            foreach ($activities as $a) {
                fputcsv($file, [
                    $a->created_at->format('Y-m-d H:i:s'),
                    $a->user_name,
                    $a->action,
                    $a->description,
                    $a->page,
                    $a->status,
                ]);
            }
            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
