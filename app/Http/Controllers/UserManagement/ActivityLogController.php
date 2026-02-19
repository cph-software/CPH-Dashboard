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
            ->where('project', 'CPH Dashboard')
            ->orderBy('created_at', 'desc');

        // Simple Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('activity', 'like', "%$search%")
                  ->orWhere('module', 'like', "%$search%")
                  ->orWhere('action_type', 'like', "%$search%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%$search%")
                    ->orWhereHas('karyawan', function($qk) use ($search) {
                        $qk->where('full_name', 'like', "%$search%")
                           ->orWhere('nama', 'like', "%$search%")
                           ->orWhere('employee_name', 'like', "%$search%");
                    });
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

        return response()->json($log);
    }
}
