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

Route::get('/debug-menus', function () {
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

    // Route::prefix('cph_dashboard')->group(function () {
    //     // Moved to master_data_tyre
    // });

    // Tyre Performance Application Routes
    Route::prefix('master_data_tyre')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'index'])->name('master_data.dashboard')->middleware('tyre.permission:Dashboard');
        Route::get('/dashboard/drill-down', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'drillDown'])->name('master_data.drill-down')->middleware('tyre.permission:Dashboard');
        Route::get('/dashboard/brand-performance', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'brandPerformanceAjax'])->name('master_data.brand-performance')->middleware('tyre.permission:Dashboard');
        Route::get('/dashboard/cpk-by-brand', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'cpkByBrandAjax'])->name('master_data.cpk-by-brand')->middleware('tyre.permission:Dashboard');
        Route::get('/dashboard/scrap-by-position', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'scrapByPositionAjax'])->name('master_data.scrap-by-position')->middleware('tyre.permission:Dashboard');
        Route::get('/dashboard/export', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'export'])->name('master_data.export')->middleware('tyre.permission:Dashboard');
        Route::get('/dashboard/download-template', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'downloadTemplate'])->name('master_data.download-template')->middleware('tyre.permission:Dashboard');

        // Master Data Routes
        Route::resource('master_brand', \App\Http\Controllers\TyrePerformance\Master\TyreBrandController::class)->names('tyre-brands')->middleware('tyre.permission:Brands');
        Route::resource('master_size', \App\Http\Controllers\TyrePerformance\Master\TyreSizeController::class)->names('tyre-sizes')->middleware('tyre.permission:Sizes');
        Route::resource('master_segment', \App\Http\Controllers\TyrePerformance\Master\TyreSegmentController::class)->names('tyre-segments')->middleware('tyre.permission:Segments');
        Route::resource('master_location', \App\Http\Controllers\TyrePerformance\Master\TyreLocationController::class)->names('tyre-locations')->middleware('tyre.permission:Locations');
        Route::resource('master_failure_code', \App\Http\Controllers\TyrePerformance\Master\TyreFailureCodeController::class)->names('tyre-failure-codes')->middleware('tyre.permission:Failure Codes');
        Route::resource('master_position', \App\Http\Controllers\TyrePerformance\Master\TyrePositionController::class)->names('tyre-positions')->middleware('tyre.permission:Position Layouts');
        Route::get('master_position/{id}/layout', [\App\Http\Controllers\TyrePerformance\Master\TyrePositionController::class, 'getLayout'])->name('tyre-positions.layout')->middleware('tyre.permission:Position Layouts');
        Route::get('master_tyre/data', [\App\Http\Controllers\TyrePerformance\Master\TyreMasterController::class, 'data'])->name('tyre-master.data')->middleware('tyre.permission:Master Tyre');
        Route::resource('master_tyre', \App\Http\Controllers\TyrePerformance\Master\TyreMasterController::class)->names('tyre-master')->middleware('tyre.permission:Master Tyre');
        Route::get('master_kendaraan/data', [\App\Http\Controllers\TyrePerformance\Master\KendaraanController::class, 'data'])->name('tyre-kendaraan.data')->middleware('tyre.permission:Vehicle Master');
        Route::resource('master_kendaraan', \App\Http\Controllers\TyrePerformance\Master\KendaraanController::class)->names('tyre-kendaraan')->middleware('tyre.permission:Vehicle Master');

        // Tyre Movement Routes
        Route::get('movement', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'index'])->name('tyre-movement.index')->middleware('tyre.permission:Movement History');
        Route::get('pemasangan', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'pemasangan'])->name('tyre-movement.pemasangan')->middleware('tyre.permission:Pemasangan (Install)');
        Route::get('pelepasan', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'pelepasan'])->name('tyre-movement.pelepasan')->middleware('tyre.permission:Pelepasan (Remove)');
        Route::get('layout/{id}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getVehicleLayout'])->middleware('tyre.permission:Tyre Operations');
        Route::get('position-info', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getPositionInfo'])->middleware('tyre.permission:Tyre Operations');
        Route::get('vehicle-detail/{id}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getVehicleDetail'])->middleware('tyre.permission:Tyre Operations');
        Route::get('search-tyres', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'searchTyres'])->name('tyre-movement.search-tyres')->middleware('tyre.permission:Tyre Operations');
        Route::get('segments/{locationId}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getSegmentsByLocation'])->name('tyre-movement.get-segments')->middleware('tyre.permission:Tyre Operations');
        Route::get('history', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'apiHistory'])->name('tyre-movement.history')->middleware('tyre.permission:Movement History');
        Route::delete('rollback/{id}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'rollback'])->name('tyre-movement.rollback')->middleware('tyre.permission:Movement History,delete');
        Route::post('store', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'store'])->middleware('tyre.permission:Tyre Operations,create');

        // Tyre Examination Routes
        Route::get('examination/data', [\App\Http\Controllers\TyrePerformance\Examination\TyreExaminationController::class, 'data'])->name('examination.data')->middleware('tyre.permission:Examination');
        Route::get('examination/vehicle-tyres/{vehicleId}', [\App\Http\Controllers\TyrePerformance\Examination\TyreExaminationController::class, 'getVehicleTyres'])->name('examination.get-vehicle-tyres')->middleware('tyre.permission:Examination');
        Route::get('examination/{id}/export-pdf', [\App\Http\Controllers\TyrePerformance\Examination\TyreExaminationController::class, 'exportPdf'])->name('examination.export-pdf')->middleware('tyre.permission:Examination');
        Route::resource('examination', \App\Http\Controllers\TyrePerformance\Examination\TyreExaminationController::class)->middleware('tyre.permission:Examination');

        Route::resource('master_pattern', \App\Http\Controllers\TyrePerformance\Master\TyrePatternController::class)->names('tyre-patterns')->middleware('tyre.permission:Patterns');

        // User Management Routes (Moved from cph_dashboard)
        Route::resource('roles', \App\Http\Controllers\UserManagement\RoleController::class);
        Route::resource('menus', \App\Http\Controllers\UserManagement\MenuController::class);
        Route::resource('users', \App\Http\Controllers\UserManagement\UserController::class);

        // Permission Management
        Route::get('permissions', [\App\Http\Controllers\UserManagement\PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/get', [\App\Http\Controllers\UserManagement\PermissionController::class, 'getPermissions'])->name('permissions.get');
        Route::post('permissions', [\App\Http\Controllers\UserManagement\PermissionController::class, 'store'])->name('permissions.store');

        // Import Approval
        Route::get('import-approval', [\App\Http\Controllers\UserManagement\ImportApprovalController::class, 'index'])->name('import-approval.index');
        Route::get('import-approval/{id}', [\App\Http\Controllers\UserManagement\ImportApprovalController::class, 'show'])->name('import-approval.show');
        Route::post('import-approval/{id}/approve', [\App\Http\Controllers\UserManagement\ImportApprovalController::class, 'approve'])->name('import-approval.approve');
        Route::post('import-approval/{id}/reject', [\App\Http\Controllers\UserManagement\ImportApprovalController::class, 'reject'])->name('import-approval.reject');

        // Import Action (Uploader)
        Route::post('import-data', [\App\Http\Controllers\UserManagement\ImportController::class, 'storeCSV'])->name('import.store');

        // Activity Logs
        Route::get('activity-logs', [\App\Http\Controllers\UserManagement\ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('tyre.permission:All Activity');
        Route::get('activity-logs/{id}', [\App\Http\Controllers\UserManagement\ActivityLogController::class, 'show'])->name('activity-logs.show')->middleware('tyre.permission:All Activity');

        // Coming Soon for this prefix
        Route::get('/coming-soon', function () {
            return view('pages.under-development', ['featureName' => request('feature')]);
        });
    });

    // Coming Soon / Under Development Page
    Route::get('/coming-soon', function () {
        return view('pages.under-development', ['featureName' => request('feature')]);
    })->name('coming-soon');
});
