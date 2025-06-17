<?php

use App\Http\Controllers\Delivery\AuthController;
use App\Http\Controllers\Delivery\DeliveryHomeController;
use App\Http\Controllers\Delivery\ExpressController;
use App\Http\Controllers\Delivery\MapController;
use Illuminate\Support\Facades\Route;


Route::prefix('delivery')->group(function () {
    // ---------------------------------Public routes---------------------------------

    //auth
    Route::prefix('')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('delivery.register');
        Route::post('/login', [AuthController::class, 'login'])->name('delivery.login');

        Route::get('email/verify/{id}', [AuthController::class, 'verify'])->name('delivery.verification.verify');
    });


    // ---------------------------------Protected routes---------------------------------
    Route::middleware(['auth:api', 'delivery.access'])->group(function () {

        Route::get('/me', [AuthController::class, 'me'])->name('delivery.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('delivery.logout');

        Route::get('/home', [DeliveryHomeController::class, 'index'])->name('delivery.home');

        Route::prefix('express/history')->group(function () {
            Route::get('', [ExpressController::class, 'history'])->name('delivery.express.history');
            Route::get('/{id}', [ExpressController::class, 'showHistory'])->name('delivery.express.show-history');
        });

        Route::prefix('express')->group(function () {
            Route::get('/', [ExpressController::class, 'index'])->name('delivery.express.index');
            Route::get('/{id}', [ExpressController::class, 'show'])->name('delivery.express.show');
            Route::post('/pickup', [DeliveryHomeController::class, 'pickupPackage'])->name('delivery.pickup-package');
            Route::post('/delivered', [DeliveryHomeController::class, 'deliveredPackage'])->name('delivery.delivered-package');
        });

        //realtime tracking post
        Route::post('/tracking', [DeliveryHomeController::class, 'realtimeTracking'])->name('delivery.realtime-tracking');

        //search map by package number
        Route::prefix('map')->group(function () {
            Route::get('/{package_number}', [MapController::class, 'searchMap'])->name('delivery.map-search');
        });
    });
});
