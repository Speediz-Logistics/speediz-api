<?php

use App\Http\Controllers\Vendor\AuthController;
use App\Http\Controllers\Vendor\InvoiceController;
use App\Http\Controllers\Vendor\PackageController;
use App\Http\Controllers\Vendor\ProfileController;
use Illuminate\Support\Facades\Route;
Route::prefix('vendor')->group(function () {
    // ---------------------------------Public routes---------------------------------

    //auth
    Route::prefix('')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('vendor.register');
        Route::post('/login', [AuthController::class, 'login'])->name('vendor.login');

        Route::post('/request-vendor', [AuthController::class, 'requestVendor'])->name('vendor.login');
    });


    // ---------------------------------Protected routes---------------------------------
    Route::middleware(['auth:api', 'vendor.access'])->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('vendor.me');

        //package
        Route::prefix('packages')->group(function () {
            Route::get('', [PackageController::class, 'index'])->name('vendor.packages.index');
            //history
            Route::get('history', [PackageController::class, 'history'])->name('vendor.packages.history');
            Route::post('', [PackageController::class, 'store'])->name('vendor.packages.store');
            Route::get('{id}', [PackageController::class, 'show'])->name('vendor.packages.show');
            Route::post('{id}', [PackageController::class, 'update'])->name('vendor.packages.update');
            Route::delete('{id}', [PackageController::class, 'destroy'])->name('vendor.packages.destroy');

            //search package by number
            Route::get('search/{number}', [PackageController::class, 'search'])->name('vendor.packages.search');
            //map
            Route::get('map/{id}', [PackageController::class, 'map'])->name('vendor.packages.map');
        });

        Route::prefix('profile')->group(function () {
            Route::get('', [ProfileController::class, 'index'])->name('vendor.profile.index');
            //update profile
            Route::post('', [ProfileController::class, 'update'])->name('vendor.profile.update');
            //reset password
            Route::post('reset-password', [ProfileController::class, 'resetPassword'])->name('vendor.profile.reset-password');
            //logout
            Route::post('logout', [ProfileController::class, 'logout'])->name('vendor.profile.logout');
        });

        //Invoice
        Route::prefix('invoice')->group(function () {
            Route::get('', [InvoiceController::class, 'index'])->name('vendor.invoice.index');
            Route::get('{id}', [InvoiceController::class, 'show'])->name('vendor.invoice.show');
        });

        //Vendor Invoice
        Route::prefix('vendor-invoice')->group(function () {
            Route::get('', [InvoiceController::class, 'vendorInvoice'])->name('vendor.vendor-invoice.index');
            Route::get('{id}', [InvoiceController::class, 'vendorInvoiceShow'])->name('vendor.vendor-invoice.show');
        });
    });
});
