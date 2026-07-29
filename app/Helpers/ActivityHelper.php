<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class ActivityHelper
{
    public static function log($action, $description, $page, $status = 'info', $metadata = null)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
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
            'metadata' => $metadata,
        ]);
    }
}
