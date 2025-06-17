<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryUserController;
use App\Http\Controllers\Admin\EmployeeUserController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TrackingController;
use App\Http\Controllers\Admin\VendorUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    // ---------------------------------Public routes---------------------------------

    Route::prefix('')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('vendor.register');
        Route::post('/login', [AuthController::class, 'login'])->name('vendor.login');
    });

    // ---------------------------------Protected routes---------------------------------
    Route::middleware(['auth:api', 'admin.access'])->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('vendor.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('vendor.logout');

        //dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::prefix('/package-invoice')->group(function () {
            Route::get('/', [InvoiceController::class, 'packagesInvoice'])->name('admin.package.invoice');
            Route::get('/{id}', [InvoiceController::class, 'showPackagesInvoice'])->name('admin.package.invoice.show');
            //updatePackageInvoice
            Route::post('/{id}', [InvoiceController::class, 'updatePackageInvoice'])->name('admin.package.invoice.update');
        });

        Route::prefix('/vendor-invoice')->group(function () {
            Route::get('/', [InvoiceController::class, 'vendorInvoice'])->name('admin.vendor.invoice');
            Route::get('/{id}', [InvoiceController::class, 'showVendorInvoice'])->name('admin.vendor.invoice.show');
        });

        //user management
        Route::prefix('vendors')->group(function () {
            Route::get('/', [VendorUserController::class, 'index'])->name('admin.vendors');
            Route::post('/', [VendorUserController::class, 'store'])->name('admin.vendors.store');
            Route::get('/{id}', [VendorUserController::class, 'show'])->name('admin.vendors.show');
            Route::post('/{id}', [VendorUserController::class, 'update'])->name('admin.vendors.update');
            Route::delete('/{id}', [VendorUserController::class, 'destroy'])->name('admin.vendors.delete');
        });

        Route::prefix('employees')->group(function () {
            Route::get('/', [EmployeeUserController::class, 'index'])->name('admin.employees');
            Route::post('/', [EmployeeUserController::class, 'store'])->name('admin.employees.store');
            Route::get('/{id}', [EmployeeUserController::class, 'show'])->name('admin.employees.show');
            Route::post('/{id}', [EmployeeUserController::class, 'update'])->name('admin.employees.update');
            Route::delete('/{id}', [EmployeeUserController::class, 'destroy'])->name('admin.employees.delete');
        });

        Route::prefix('drivers')->group(function () {
            Route::get('/', [DeliveryUserController::class, 'index'])->name('admin.drivers.users');
            Route::post('/', [DeliveryUserController::class, 'store'])->name('admin.drivers.users.store');
            Route::get('/{id}', [DeliveryUserController::class, 'show'])->name('admin.drivers.users.show');
            Route::post('/{id}', [DeliveryUserController::class, 'update'])->name('admin.drivers.users.update');
            Route::delete('/{id}', [DeliveryUserController::class, 'destroy'])->name('admin.drivers.users.delete');
        });

        //tracking
        Route::prefix('tracking')->group(function () {
            Route::get('/', [TrackingController::class, 'index'])->name('drivers.tracking');
            Route::get('/{id}', [TrackingController::class, 'show'])->name('drivers.tracking.show');
        });


        //setting
        Route::prefix('setting')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('admin.setting');
            Route::post('/', [SettingController::class, 'update'])->name('admin.setting.update');
        });
    });
});
