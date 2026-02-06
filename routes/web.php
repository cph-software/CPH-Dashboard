<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('login/{type?}', [LoginController::class, 'login'])->name('login')->middleware('guest');
Route::post('login', [LoginController::class, 'postLogin']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard & Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Example of using permission middleware
    // Route::get('/tyre', [TyreController::class, 'index'])->middleware('permission:Tyre Master,view');
});
