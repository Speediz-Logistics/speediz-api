<?php

use App\Http\Controllers\Employee\InvoiceController;
use App\Http\Controllers\Employee\SettingController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\Employee\AuthController;
use App\Http\Controllers\Employee\DriverManagementController;
use App\Http\Controllers\Employee\PackageController;
use App\Http\Controllers\Employee\PackageOptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('employee')->group(function () {
    // ---------------------------------Public routes---------------------------------
    Route::prefix('')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('employee.register');
        Route::post('/login', [AuthController::class, 'login'])->name('employee.login');
    });

    // ---------------------------------Protected routes---------------------------------
    Route::middleware(['auth:api', 'employee.access'])->group(function () {
        //get me
        Route::get('/me', [AuthController::class, 'me'])->name('employee.me');
        //assign driver to package
        Route::post('/assign-driver', [DriverManagementController::class, 'assignDriver'])->name('employee.assign-driver');
        Route::post('/create-invoice', [DriverManagementController::class, 'createVendorInvoice'])->name('employee.create-invoice');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::prefix('package')->group(function () {
            Route::get('/', [PackageController::class, 'index'])->name('employee.package.index');
            //store
            Route::post('/', [PackageController::class, 'store'])->name('employee.package.store');
            Route::get('/{id}', [PackageController::class, 'show'])->name('employee.package.show');
            Route::post('/{id}', [PackageController::class, 'update'])->name('employee.package.update');
        });
        Route::post('/package-options', [PackageOptionController::class, 'index'])->name('employee.package.options');
        Route::post('/package-search', [PackageController::class, 'search'])->name('employee.package.search');

        Route::prefix('setting')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('employee.setting');
            Route::post('/', [SettingController::class, 'update'])->name('employee.setting.update');
        });

        //vendor invoice
        Route::prefix('/package-invoice')->group(function () {
            Route::get('/', [InvoiceController::class, 'packagesInvoice'])->name('employee.package.invoice');
            //updatePackageInvoice
            Route::post('/{id}', [InvoiceController::class, 'updatePackageInvoice'])->name('employee.package.invoice.update');
        });

        Route::prefix('/vendor-invoice')->group(function () {
            Route::get('/', [InvoiceController::class, 'vendorInvoice'])->name('employee.vendor.invoice');
            //add create
        });
    });


});
