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
        $redirectUrl = getDashboardRedirectUrl();
        if ($redirectUrl !== '/dashboard') {
            return redirect($redirectUrl);
        }
        return view('dashboard');
    })->name('dashboard');

    // Also add /home as requested
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

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

    // Tyre Performance Application Routes
    Route::prefix('tyre_performance')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'index'])->name('tyre_performance.dashboard');
        
        // Master Data Routes
        Route::prefix('master')->group(function () {
            Route::resource('brands', \App\Http\Controllers\TyrePerformance\Master\TyreBrandController::class)->names('tyre-brands');
            Route::resource('sizes', \App\Http\Controllers\TyrePerformance\Master\TyreSizeController::class)->names('tyre-sizes');
            Route::resource('segments', \App\Http\Controllers\TyrePerformance\Master\TyreSegmentController::class)->names('tyre-segments');
            Route::resource('locations', \App\Http\Controllers\TyrePerformance\Master\TyreLocationController::class)->names('tyre-locations');
            Route::resource('failure-codes', \App\Http\Controllers\TyrePerformance\Master\TyreFailureCodeController::class)->names('tyre-failure-codes');
            Route::resource('positions', \App\Http\Controllers\TyrePerformance\Master\TyrePositionController::class)->names('tyre-positions');
            Route::resource('tyres', \App\Http\Controllers\TyrePerformance\Master\TyreMasterController::class)->names('tyre-master');
            Route::resource('kendaraan', \App\Http\Controllers\TyrePerformance\Master\KendaraanController::class)->names('tyre-kendaraan');
            Route::resource('patterns', \App\Http\Controllers\TyrePerformance\Master\TyrePatternController::class)->names('tyre-patterns');
        });
    });

    // Example of using permission middleware
    // Route::get('/tyre', [TyreController::class, 'index'])->middleware('permission:Tyre Master,view');
});
