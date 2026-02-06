<?php

if (!function_exists('format_rupiah')) {
    function format_rupiah($number)
    {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd M Y')
    {
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('setLogActivity')) {
    function setLogActivity($userId, $message)
    {
        // For now, we can just log to laravel.log or ignore if table doesn't exist
        \Log::info("User ID {$userId}: {$message}");

        // If you have an ActivityLog model, you can do:
        // \App\Models\ActivityLog::create(['user_id' => $userId, 'activity' => $message]);
    }
}
