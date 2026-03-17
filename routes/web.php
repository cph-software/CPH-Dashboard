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

// ======================================================================
// PUBLIC ONBOARDING PORTAL (PROJECT CODE BASED)
// ======================================================================
Route::get('/onboarding', [\App\Http\Controllers\PublicPortal\OnboardingController::class, 'index'])->name('public.onboarding.index');
Route::post('/onboarding/verify', [\App\Http\Controllers\PublicPortal\OnboardingController::class, 'verify'])->name('public.onboarding.verify');
Route::get('/onboarding/{code}', [\App\Http\Controllers\PublicPortal\OnboardingController::class, 'show'])->name('public.onboarding.show');
Route::get('/onboarding/{code}/success', [\App\Http\Controllers\PublicPortal\OnboardingController::class, 'success'])->name('public.onboarding.success');
Route::post('/onboarding/{code}/save', [\App\Http\Controllers\PublicPortal\OnboardingController::class, 'save'])->name('public.onboarding.save');
Route::post('/onboarding/{code}/upload', [\App\Http\Controllers\PublicPortal\OnboardingController::class, 'upload'])->name('public.onboarding.upload');

// Dashboard & Protected Routes
Route::middleware(['auth'])->group(function () {

    // ======================================================================
    // GENERAL
    // ======================================================================
    Route::get('/dashboard', function () {
        $redirectUrl = getDashboardRedirectUrl();
        if ($redirectUrl !== '/dashboard') {
            return redirect($redirectUrl);
        }
        return view('dashboard');
    })->name('dashboard');

    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/coming-soon', function () {
        return view('pages.under-development', ['featureName' => request('feature')]);
    })->name('coming-soon');

    // ======================================================================
    // TYRE PERFORMANCE — Dashboard
    // ======================================================================
    Route::get('tyre-dashboard', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'index'])->name('master_data.dashboard')->middleware('tyre.permission:Dashboard');
    Route::get('tyre-dashboard/drill-down', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'drillDown'])->name('master_data.drill-down')->middleware('tyre.permission:Dashboard');
    Route::get('tyre-dashboard/brand-performance', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'brandPerformanceAjax'])->name('master_data.brand-performance')->middleware('tyre.permission:Dashboard');
    Route::get('tyre-dashboard/brand-detail-performance', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'brandDetailPerformanceAjax'])->name('master_data.brand-detail-performance')->middleware('tyre.permission:Dashboard');
    Route::get('tyre-dashboard/cpk-by-brand', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'cpkByBrandAjax'])->name('master_data.cpk-by-brand')->middleware('tyre.permission:Dashboard');
    Route::get('tyre-dashboard/scrap-by-position', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'scrapByPositionAjax'])->name('master_data.scrap-by-position')->middleware('tyre.permission:Dashboard');
    Route::get('tyre-dashboard/export', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'export'])->name('master_data.export')->middleware('tyre.permission:Dashboard');
    Route::get('tyre-dashboard/download-template', [\App\Http\Controllers\TyrePerformance\DashboardController::class, 'downloadTemplate'])->name('master_data.download-template')->middleware('tyre.permission:Dashboard');

    // ======================================================================
    // TYRE PERFORMANCE — Master Data
    // ======================================================================
    Route::resource('master_brand', \App\Http\Controllers\TyrePerformance\Master\TyreBrandController::class)->names('tyre-brands')->middleware('tyre.permission:Brands');
    Route::resource('master_size', \App\Http\Controllers\TyrePerformance\Master\TyreSizeController::class)->names('tyre-sizes')->middleware('tyre.permission:Sizes');
    Route::resource('master_segment', \App\Http\Controllers\TyrePerformance\Master\TyreSegmentController::class)->names('tyre-segments')->middleware('tyre.permission:Segments');
    Route::resource('master_location', \App\Http\Controllers\TyrePerformance\Master\TyreLocationController::class)->names('tyre-locations')->middleware('tyre.permission:Locations');
    Route::resource('master_failure_code', \App\Http\Controllers\TyrePerformance\Master\TyreFailureCodeController::class)->names('tyre-failure-codes')->middleware('tyre.permission:Failure Codes');
    Route::resource('master_pattern', \App\Http\Controllers\TyrePerformance\Master\TyrePatternController::class)->names('tyre-patterns')->middleware('tyre.permission:Patterns');
    Route::resource('master_company', \App\Http\Controllers\TyrePerformance\Master\TyreCompanyController::class)->names('tyre-companies')->middleware('tyre.permission:Master Tyre');
    Route::get('master_company/{id}/mapping', [\App\Http\Controllers\TyrePerformance\Master\TyreCompanyController::class, 'mapping'])->name('tyre-companies.mapping')->middleware('tyre.permission:Master Tyre');
    Route::post('master_company/{id}/mapping', [\App\Http\Controllers\TyrePerformance\Master\TyreCompanyController::class, 'updateMapping'])->name('tyre-companies.update-mapping')->middleware('tyre.permission:Master Tyre');
    Route::post('failure-aliases', [\App\Http\Controllers\TyrePerformance\Master\TyreFailureAliasController::class, 'store'])->name('tyre-failure-aliases.store')->middleware('tyre.permission:Failure Codes');
    Route::delete('failure-aliases/{id}', [\App\Http\Controllers\TyrePerformance\Master\TyreFailureAliasController::class, 'destroy'])->name('tyre-failure-aliases.destroy')->middleware('tyre.permission:Failure Codes');
    Route::resource('master_position', \App\Http\Controllers\TyrePerformance\Master\TyrePositionController::class)->names('tyre-positions')->middleware('tyre.permission:Position Layouts');
    Route::get('master_position/{id}/layout', [\App\Http\Controllers\TyrePerformance\Master\TyrePositionController::class, 'getLayout'])->name('tyre-positions.layout')->middleware('tyre.permission:Position Layouts');
    Route::get('master_tyre/data', [\App\Http\Controllers\TyrePerformance\Master\TyreMasterController::class, 'data'])->name('tyre-master.data')->middleware('tyre.permission:Master Tyre');
    Route::post('master_tyre/bulk-action', [\App\Http\Controllers\TyrePerformance\Master\TyreMasterController::class, 'bulkAction'])->name('tyre-master.bulk-action')->middleware('tyre.permission:Master Tyre');
    Route::resource('master_tyre', \App\Http\Controllers\TyrePerformance\Master\TyreMasterController::class)->names('tyre-master')->middleware('tyre.permission:Master Tyre');
    Route::get('master_kendaraan/data', [\App\Http\Controllers\TyrePerformance\Master\KendaraanController::class, 'data'])->name('tyre-kendaraan.data')->middleware('tyre.permission:Vehicle Master');
    Route::post('master_kendaraan/bulk-action', [\App\Http\Controllers\TyrePerformance\Master\KendaraanController::class, 'bulkAction'])->name('tyre-kendaraan.bulk-action')->middleware('tyre.permission:Vehicle Master');
    Route::resource('master_kendaraan', \App\Http\Controllers\TyrePerformance\Master\KendaraanController::class)->names('tyre-kendaraan')->middleware('tyre.permission:Vehicle Master');

    // ======================================================================
    // TYRE PERFORMANCE — Movement
    // ======================================================================
    Route::get('movement', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'index'])->name('tyre-movement.index')->middleware('tyre.permission:Movement History');
    Route::get('pemasangan', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'pemasangan'])->name('tyre-movement.pemasangan')->middleware('tyre.permission:Pemasangan (Install)');
    Route::get('pelepasan', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'pelepasan'])->name('tyre-movement.pelepasan')->middleware('tyre.permission:Pelepasan (Remove)');
    Route::get('rotasi', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'rotasi'])->name('tyre-movement.rotasi')->middleware('tyre.permission:Rotasi (Rotate)');
    Route::get('layout/{id}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getVehicleLayout'])->middleware('tyre.permission:Tyre Operations');
    Route::get('position-info', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getPositionInfo'])->middleware('tyre.permission:Tyre Operations');
    Route::get('vehicle-detail/{id}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getVehicleDetail'])->middleware('tyre.permission:Tyre Operations');
    Route::get('search-tyres', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'searchTyres'])->name('tyre-movement.search-tyres')->middleware('tyre.permission:Tyre Operations');
    Route::get('segments/{locationId}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'getSegmentsByLocation'])->name('tyre-movement.get-segments')->middleware('tyre.permission:Tyre Operations');
    Route::get('tyre-detail', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'tyreDetail'])->name('tyre-movement.tyre-detail')->middleware('tyre.permission:Tyre Operations');
    Route::get('history', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'apiHistory'])->name('tyre-movement.history')->middleware('tyre.permission:Movement History');
    Route::delete('rollback/{id}', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'rollback'])->name('tyre-movement.rollback')->middleware('tyre.permission:Movement History,delete');
    Route::post('tyre-store', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'store'])->name('tyre-movement.store')->middleware('tyre.permission:Tyre Operations,create');
    Route::post('set-active-company', [\App\Http\Controllers\TyrePerformance\Movement\TyreMovementController::class, 'setActiveCompany'])->name('tyre-movement.set-active-company')->middleware('tyre.permission:Tyre Operations');

    // ======================================================================
    // TYRE PERFORMANCE — Monitoring
    // ======================================================================
    Route::get('monitoring/data', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'data'])->name('monitoring.data')->middleware('tyre.permission:Tyre Monitoring');
    Route::get('monitoring/tyre-by-serial', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'getTyreBySerial'])->name('monitoring.tyre-by-serial')->middleware('tyre.permission:Tyre Monitoring');
    Route::get('monitoring/sessions/{id}/export', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'export'])->name('monitoring.sessions.export')->middleware('tyre.permission:Tyre Monitoring');
    Route::get('monitoring/sessions/{id}/export-pdf', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'exportPdf'])->name('monitoring.sessions.export-pdf')->middleware('tyre.permission:Tyre Monitoring');
    Route::get('monitoring/vehicle/{id}', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'showVehicle'])->name('monitoring.vehicle.show')->middleware('tyre.permission:Tyre Monitoring');
    Route::get('monitoring/session/{id}', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'showSession'])->name('monitoring.sessions.show')->middleware('tyre.permission:Tyre Monitoring');
    Route::get('monitoring/master-vehicle/{id}', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'getMasterVehicleDetails'])->name('monitoring.master-vehicle.details')->middleware('tyre.permission:Tyre Monitoring');

    Route::post('monitoring/vehicle', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'storeVehicle'])->name('monitoring.vehicle.store')->middleware('tyre.permission:Tyre Monitoring');
    Route::put('monitoring/vehicle/{id}', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'updateVehicle'])->name('monitoring.vehicle.update')->middleware('tyre.permission:Tyre Monitoring');
    Route::delete('monitoring/vehicle/{id}', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'destroyVehicle'])->name('monitoring.vehicle.destroy')->middleware('tyre.permission:Tyre Monitoring');
    Route::get('monitoring/sessions/create/{vehicle_id}', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'createSession'])->name('monitoring.sessions.create')->middleware('tyre.permission:Tyre Monitoring');
    Route::get('monitoring/sessions/add-check/{session_id}', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'createCheck'])->name('monitoring.check.create')->middleware('tyre.permission:Tyre Monitoring');
    Route::post('monitoring/session', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'storeSession'])->name('monitoring.sessions.store')->middleware('tyre.permission:Tyre Monitoring');
    Route::put('monitoring/session/{id}', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'updateSession'])->name('monitoring.sessions.update')->middleware('tyre.permission:Tyre Monitoring');
    Route::delete('monitoring/session/{id}', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'destroySession'])->name('monitoring.sessions.destroy')->middleware('tyre.permission:Tyre Monitoring');

    Route::post('monitoring/installation', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'storeInstallation'])->name('monitoring.installation.store')->middleware('tyre.permission:Tyre Monitoring');
    Route::post('monitoring/check', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'storeBatchCheck'])->name('monitoring.check.store')->middleware('tyre.permission:Tyre Monitoring');
    Route::post('monitoring/removal', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'storeRemoval'])->name('monitoring.removal.store')->middleware('tyre.permission:Tyre Monitoring');
    Route::post('monitoring/upload-image', [\App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class, 'uploadImage'])->name('monitoring.upload-image')->middleware('tyre.permission:Tyre Monitoring');

    Route::resource('monitoring', \App\Http\Controllers\TyrePerformance\Monitoring\MonitoringController::class)->only(['index'])->middleware('tyre.permission:Tyre Monitoring');

    // ======================================================================
    // TYRE PERFORMANCE — Examination
    // ======================================================================
    Route::get('examination/data', [\App\Http\Controllers\TyrePerformance\Examination\TyreExaminationController::class, 'data'])->name('examination.data')->middleware('tyre.permission:Examination');
    Route::get('examination/vehicle-tyres/{vehicleId}', [\App\Http\Controllers\TyrePerformance\Examination\TyreExaminationController::class, 'getVehicleTyres'])->name('examination.get-vehicle-tyres')->middleware('tyre.permission:Examination');
    Route::get('examination/{id}/export-pdf', [\App\Http\Controllers\TyrePerformance\Examination\TyreExaminationController::class, 'exportPdf'])->name('examination.export-pdf')->middleware('tyre.permission:Examination');
    Route::resource('examination', \App\Http\Controllers\TyrePerformance\Examination\TyreExaminationController::class)->middleware('tyre.permission:Examination');

    // ======================================================================
    // USER MANAGEMENT
    // ======================================================================
    Route::resource('roles', \App\Http\Controllers\UserManagement\RoleController::class);
    Route::resource('menus', \App\Http\Controllers\UserManagement\MenuController::class);
    Route::resource('users', \App\Http\Controllers\UserManagement\UserController::class);
    Route::get('get-tokos', [\App\Http\Controllers\UserManagement\UserController::class, 'getTokos'])->name('users.get-tokos');

    // Permission Management
    Route::get('permissions', [\App\Http\Controllers\UserManagement\PermissionController::class, 'index'])->name('permissions.index');
    Route::get('permissions/get', [\App\Http\Controllers\UserManagement\PermissionController::class, 'getPermissions'])->name('permissions.get');
    Route::post('permissions', [\App\Http\Controllers\UserManagement\PermissionController::class, 'store'])->name('permissions.store');

    // Import Approval
    Route::get('import-approval', [\App\Http\Controllers\UserManagement\ImportApprovalController::class, 'index'])
        ->name('import-approval.index')
        ->middleware('tyre.permission:Import Approval,view');
        
    Route::get('import-approval/{id}', [\App\Http\Controllers\UserManagement\ImportApprovalController::class, 'show'])
        ->name('import-approval.show')
        ->middleware('tyre.permission:Import Approval,view');
        
    Route::post('import-approval/{id}/approve', [\App\Http\Controllers\UserManagement\ImportApprovalController::class, 'approve'])
        ->name('import-approval.approve')
        ->middleware('tyre.permission:Import Approval,update');
        
    Route::post('import-approval/{id}/reject', [\App\Http\Controllers\UserManagement\ImportApprovalController::class, 'reject'])
        ->name('import-approval.reject')
        ->middleware('tyre.permission:Import Approval,update');

    // Import Action (Uploader)
    Route::post('import-data', [\App\Http\Controllers\UserManagement\ImportController::class, 'storeCSV'])
        ->name('import.store')
        ->middleware('tyre.permission:Import Approval,create');

    // Activity Logs
    Route::get('activity-logs', [\App\Http\Controllers\UserManagement\ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('tyre.permission:All Activity');
    Route::get('activity-logs/{id}', [\App\Http\Controllers\UserManagement\ActivityLogController::class, 'show'])->name('activity-logs.show')->middleware('tyre.permission:All Activity');

   // Onboarding Management (Internal)
   Route::resource('onboarding-projects', \App\Http\Controllers\UserManagement\OnboardingController::class)->middleware('tyre.permission:Onboarding Manager');
   Route::get('onboarding-projects/{id}/download-checklist', [\App\Http\Controllers\UserManagement\OnboardingController::class, 'downloadChecklist'])->name('onboarding-projects.download-checklist')->middleware('tyre.permission:Onboarding Manager');
   Route::post('onboarding-projects/{id}/generate-accounts', [\App\Http\Controllers\UserManagement\OnboardingController::class, 'generateAccounts'])->name('onboarding-projects.generate-accounts')->middleware('tyre.permission:Onboarding Manager');
});
