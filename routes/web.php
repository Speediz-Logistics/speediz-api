<?php

use App\Http\Controllers\Delivery\AuthController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get("/error");


Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    // Find the user by ID
    $user = User::findOrFail($id);

    // Check if the hash matches
    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link.');
    }

    // Mark the user as verified
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    // Log the user in if necessary
    Auth::login($user);

    return redirect('/')->with('verified', true);
})->middleware(['signed'])->name('verification.verify');

Route::prefix('v1')->group(function () {
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])->name('delivery.verification.verify');
});
