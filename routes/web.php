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

Route::get('/debug-menus', function() {
    return \App\Models\Menu::where('url', 'like', '%master_%')->orWhere('name', 'like', '%Vehicle%')->orWhere('name', 'like', '%Kendaraan%')->get();
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
        // Master Data Routes
        Route::resource('master_brand', \App\Http\Controllers\TyrePerformance\Master\TyreBrandController::class)->names('tyre-brands');
        Route::resource('master_size', \App\Http\Controllers\TyrePerformance\Master\TyreSizeController::class)->names('tyre-sizes');
        Route::resource('master_segment', \App\Http\Controllers\TyrePerformance\Master\TyreSegmentController::class)->names('tyre-segments');
        Route::resource('master_location', \App\Http\Controllers\TyrePerformance\Master\TyreLocationController::class)->names('tyre-locations');
        Route::resource('master_failure_code', \App\Http\Controllers\TyrePerformance\Master\TyreFailureCodeController::class)->names('tyre-failure-codes');
        Route::resource('master_position', \App\Http\Controllers\TyrePerformance\Master\TyrePositionController::class)->names('tyre-positions');
        Route::get('master_position/{id}/layout', [\App\Http\Controllers\TyrePerformance\Master\TyrePositionController::class, 'getLayout'])->name('tyre-positions.layout');
        Route::get('master_tyre/data', [\App\Http\Controllers\TyrePerformance\Master\TyreMasterController::class, 'data'])->name('tyre-master.data');
        Route::resource('master_tyre', \App\Http\Controllers\TyrePerformance\Master\TyreMasterController::class)->names('tyre-master');
        Route::get('master_kendaraan/data', [\App\Http\Controllers\TyrePerformance\Master\KendaraanController::class, 'data'])->name('tyre-kendaraan.data');
        Route::resource('master_kendaraan', \App\Http\Controllers\TyrePerformance\Master\KendaraanController::class)->names('tyre-kendaraan');
        
        // Tyre Movement Routes
        Route::get('movement', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'index'])->name('tyre-movement.index');
        Route::get('movement/pemasangan', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'pemasangan'])->name('tyre-movement.pemasangan');
        Route::get('movement/pelepasan', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'pelepasan'])->name('tyre-movement.pelepasan');
        Route::get('movement/layout/{id}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getVehicleLayout']);
        Route::get('movement/position-info', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getPositionInfo']);
        Route::get('movement/vehicle-detail/{id}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getVehicleDetail']);
        Route::get('movement/search-tyres', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'searchTyres'])->name('tyre-movement.search-tyres');
        Route::get('movement/segments/{locationId}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getSegmentsByLocation'])->name('tyre-movement.get-segments');
        Route::get('movement/history', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'apiHistory'])->name('tyre-movement.history');
        Route::delete('movement/rollback/{id}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'rollback'])->name('tyre-movement.rollback');
        Route::post('movement/store', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'store']);
        Route::resource('master_pattern', \App\Http\Controllers\TyrePerformance\Master\TyrePatternController::class)->names('tyre-patterns');
    });

    // Example of using permission middleware
    // Route::get('/tyre', [TyreController::class, 'index'])->middleware('permission:Tyre Master,view');
});
