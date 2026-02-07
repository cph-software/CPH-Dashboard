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

    // CPH Dashboard Aplikasi Routes
    Route::prefix('cph_dashboard')->group(function () {
        Route::resource('roles', \App\Http\Controllers\UserManagement\RoleController::class);
        Route::resource('menus', \App\Http\Controllers\UserManagement\MenuController::class);
        Route::resource('users', \App\Http\Controllers\UserManagement\UserController::class);
        
        // Permission Management
        Route::get('permissions', [\App\Http\Controllers\UserManagement\PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/get', [\App\Http\Controllers\UserManagement\PermissionController::class, 'getPermissions'])->name('permissions.get');
        Route::post('permissions', [\App\Http\Controllers\UserManagement\PermissionController::class, 'store'])->name('permissions.store');
    });

    // Example of using permission middleware
    // Route::get('/tyre', [TyreController::class, 'index'])->middleware('permission:Tyre Master,view');
});
