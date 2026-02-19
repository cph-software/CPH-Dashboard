<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of all activities for the current project.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user.karyawan')
            ->where(function($q) {
                $q->where('project', 'CPH-Dashboard')
                  ->orWhereNull('project');
            })
            ->orderBy('created_at', 'desc');

        // Simple Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('activity', 'like', "%$search%")
                  ->orWhere('module', 'like', "%$search%")
                  ->orWhere('action_type', 'like', "%$search%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%$search%");
                  });
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('user-management.activity-logs.index', compact('logs'));
    }

    /**
     * Show detail of a specific activity log.
     */
    public function show($id)
    {
        $log = ActivityLog::with('user.karyawan')->findOrFail($id);
        
        // Manual JSON decode if needed
        $log->data_before = is_string($log->data_before) ? json_decode($log->data_before, true) : $log->data_before;
        $log->data_after = is_string($log->data_after) ? json_decode($log->data_after, true) : $log->data_after;

        return response()->json($log);
    }
}
