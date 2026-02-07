<?php

namespace App\Http\Controllers\TyrePerformance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('tyre-performance.dashboard');
    }
}
