<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LeaveRequestController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::get('/company', [CompanyController::class, 'index']);
    // Route::get('/profile-photo', [UserController::class, 'profilePhoto']);
    Route::middleware('auth:sanctum')->get('/user/profile-photo', [UserController::class, 'profilePhoto']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);

    Route::prefix('attendance')->group(function () {
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::get('/history', [AttendanceController::class, 'history']);
    });

    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::delete('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'destroy']);

    // ===== PAYROLL ROUTES =====
    // Employee PaySlip Routes
    Route::prefix('employee/payslips')->group(function () {
        Route::get('/history', [App\Http\Controllers\Api\EmployeePayslipController::class, 'getHistory']);
        Route::get('/latest', [App\Http\Controllers\Api\EmployeePayslipController::class, 'getLatest']);
        Route::get('/{payroll}', [App\Http\Controllers\Api\EmployeePayslipController::class, 'show']);
        Route::get('/{payroll}/download', [App\Http\Controllers\Api\EmployeePayslipController::class, 'download']);
        Route::post('/{payroll}/share', [App\Http\Controllers\Api\EmployeePayslipController::class, 'share']);
        Route::get('/{month}/{year}', [App\Http\Controllers\Api\EmployeePayslipController::class, 'getCurrentPayslip'])
            ->where(['month' => '[0-9]+', 'year' => '[0-9]+']);
    });

    // Admin Payroll Management Routes
    Route::prefix('payrolls')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\PayrollController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\PayrollController::class, 'store']);
        Route::post('/batch-calculate', [App\Http\Controllers\Api\PayrollController::class, 'batchCalculate']);
        Route::get('/{payroll}', [App\Http\Controllers\Api\PayrollController::class, 'show']);
        Route::put('/{payroll}', [App\Http\Controllers\Api\PayrollController::class, 'update']);
        Route::delete('/{payroll}', [App\Http\Controllers\Api\PayrollController::class, 'destroy']);
        Route::post('/{payroll}/approve', [App\Http\Controllers\Api\PayrollController::class, 'approve']);
        Route::post('/{payroll}/reject', [App\Http\Controllers\Api\PayrollController::class, 'reject']);
        Route::post('/{payroll}/mark-paid', [App\Http\Controllers\Api\PayrollController::class, 'markPaid']);
    });

    // Master Data Routes
    Route::resource('designations', App\Http\Controllers\Api\DesignationController::class);
    Route::resource('earnings', App\Http\Controllers\Api\EarningController::class);
    Route::resource('deductions', App\Http\Controllers\Api\DeductionController::class);
});
