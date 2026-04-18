<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\AcademicSessionController;
use App\Http\Controllers\Admin\OfficerController;
use App\Http\Controllers\Admin\GeoController;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Admin auth
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Protected admin routes
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::resource('students', StudentController::class);
    Route::get('students-export', [StudentController::class, 'export'])->name('students.export');
    Route::get('students-import', [StudentController::class, 'importForm'])->name('students.import.form');
    Route::post('students-import', [StudentController::class, 'import'])->name('students.import');
    Route::get('students-import-template', [StudentController::class, 'downloadTemplate'])->name('students.import.template');
    Route::get('fees-import', [StudentController::class, 'feeImportForm'])->name('students.fee.import.form');
    Route::post('fees-import', [StudentController::class, 'feeImport'])->name('students.fee.import');
    Route::get('fees-import-template', [StudentController::class, 'feeTemplate'])->name('students.fee.template');
    Route::post('students/{student}/restore', [StudentController::class, 'restore'])->name('students.restore');
    Route::post('students/session-rollover', [AcademicSessionController::class, 'rollover'])->name('sessions.rollover');

    // Student fees
    Route::post('students/{student}/fees', [StudentController::class, 'storeFee'])->name('students.fees.store');
    Route::put('students/{student}/fees/{fee}', [StudentController::class, 'updateFee'])->name('students.fees.update');

    // Departments
    Route::resource('departments', DepartmentController::class)->except(['show']);

    // Academic sessions
    Route::resource('sessions', AcademicSessionController::class)->except(['show']);
    Route::post('sessions/{session}/set-current', [AcademicSessionController::class, 'setCurrent'])->name('sessions.set-current');

    // Officers
    Route::resource('officers', OfficerController::class)->except(['show']);

    // Geo API (AJAX for cascading dropdowns)
    Route::get('api/lgas/{state}', [GeoController::class, 'lgas'])->name('api.lgas');
    Route::get('api/towns/{lga}', [GeoController::class, 'towns'])->name('api.towns');
});
