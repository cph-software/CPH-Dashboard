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
        return $this->buildLogView($request, 'Log Aktivitas Sistem', 'Memantau seluruh jejak aktivitas pengguna di dalam sistem.');
    }

    public function importExportLogs(Request $request)
    {
        // Force filter to show only import/export related logs
        $query = ActivityLog::with('user.karyawan')
            ->where('project', 'CPH Dashboard')
            ->where(function($q) {
                $q->where('module', 'like', '%Import%')
                  ->orWhere('module', 'like', '%Export%')
                  ->orWhere('module', 'like', '%Backup%')
                  ->orWhere('action_type', 'import')
                  ->orWhere('action_type', 'export');
            })
            ->orderBy('created_at', 'desc');

        return $this->processLogRequest($request, $query, 'Import & Export Log', 'Riwayat aktivitas terkait impor data, persetujuan impor, dan backup/export.');
    }

    public function editHistoryLogs(Request $request)
    {
        // Force filter to show only updates
        $query = ActivityLog::with('user.karyawan')
            ->where('project', 'CPH Dashboard')
            ->where('action_type', 'update')
            ->orderBy('created_at', 'desc');

        return $this->processLogRequest($request, $query, 'Edit History', 'Riwayat perubahan data (update) pada berbagai modul.');
    }

    private function buildLogView(Request $request, $title, $description)
    {
        $query = ActivityLog::with('user.karyawan')
            ->where('project', 'CPH Dashboard')
            ->orderBy('created_at', 'desc');

        return $this->processLogRequest($request, $query, $title, $description);
    }

    private function processLogRequest(Request $request, $query, $pageTitle, $pageDescription)
    {
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
                           ->orWhere('employee_id', 'like', "%$search%");
                    });
                  });
            });
        }

        // Filter By Module
        if ($request->filled('module')) {
            $query->ofModule($request->module);
        }

        // Filter By Action Type
        if ($request->filled('action_type')) {
            $query->ofType($request->action_type);
        }

        // Filter By Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        // Export functionality
        if ($request->get('export') == 'true') {
            $exportLogs = $query->get();
            $filename = "activity_logs_" . date('Ymd_His') . ".csv";
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];
            
            $callback = function() use($exportLogs) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Tanggal', 'Waktu', 'Pengguna', 'Aktivitas', 'Tipe Aksi', 'Modul', 'IP Address']);
                foreach ($exportLogs as $log) {
                    $userName = 'System';
                    if ($log->user) {
                        $userName = $log->user->name;
                        if ($log->user->karyawan) {
                            $userName = $log->user->karyawan->full_name ?? $log->user->karyawan->nama ?? $log->user->karyawan->employee_name ?? $userName;
                        }
                    }
                    fputcsv($file, [
                        $log->created_at->format('Y-m-d'),
                        $log->created_at->format('H:i:s'),
                        $userName,
                        $log->activity,
                        $log->action_type,
                        $log->module,
                        $log->ip_address
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        $logs = $query->paginate(25)->withQueryString();

        $modules = ActivityLog::where('project', 'CPH Dashboard')->select('module')->whereNotNull('module')->where('module', '!=', '')->distinct()->pluck('module');
        $actionTypes = ActivityLog::where('project', 'CPH Dashboard')->select('action_type')->whereNotNull('action_type')->where('action_type', '!=', '')->distinct()->pluck('action_type');

        return view('user-management.activity-logs.index', compact('logs', 'modules', 'actionTypes', 'pageTitle', 'pageDescription'));
    }

    /**
     * Show detail of a specific activity log.
     */
    public function show($id)
    {
        $log = ActivityLog::with('user.karyawan')
            ->where('project', 'CPH Dashboard')
            ->findOrFail($id);

        return response()->json($log);
    }
}
