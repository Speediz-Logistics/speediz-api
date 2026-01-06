<?php

use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\Mobile\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    // ---------------------------------Public routes---------------------------------
    Route::prefix('')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('vendor.login');
    });

    Route::post('send', [FirebaseController::class, 'sendTestNotification'])->name('firebase.sendNotification');

    Route::post('send-to-device', [FirebaseController::class, 'sendToDevice'])->name('firebase.sendToDevice');

});
