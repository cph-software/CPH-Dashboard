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
