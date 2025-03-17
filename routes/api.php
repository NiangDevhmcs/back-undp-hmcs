<?php

use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerifyController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Authenticate;
use Illuminate\Http\Request;

Route::withoutMiddleware([Authenticate::class])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::get('/app-setting', [SettingController::class, 'index']);
    Route::post('/register', [RegisterController::class, 'register']);
    // Route::post('register', [AuthController::class, 'register']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/check-otp', [AuthController::class, 'checkOtp']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('reset-password', [ResetPasswordController::class, 'reset']);

    Route::get('/email/verify/{id}/{hash}', [EmailVerifyController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [EmailVerifyController::class, 'resend'])->name('verification.resend');

    Route::get('reset-password/{token}', function (string $token) {
        return redirect(env('FRONTEND_URL') . '/reset-password?token=' . $token);
    })->name('password.reset');

    // Routes de vérification d'email Fortify pour API
    // Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    //     ->middleware(['signed', 'throttle:6,1'])
    //     ->name('verification.verify');
});







// Route pour envoyer l'email de vérification
Route::middleware('auth:sanctum')->post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->name('verification.send');

// Route::middleware(['auth:sanctum', 'verified'])->group(function () {
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/test', function(){
        return "niang";
    });
    Route::get('/authenticate', [AuthController::class, 'authenticate']);
    Route::get('/roles', [RoleController::class, 'index']);

    Route::prefix('users')->group(function () {
        Route::get('/all/{id}', [UserController::class, 'index']);
        Route::get('/users-by-tenant', [UserController::class, 'usersByTenant']);
        Route::post('/add', [UserController::class, 'store']);
        Route::put('/update/{user_id}', [UserController::class, 'updateUser']);
        Route::delete('/delete/{user_id}', [UserController::class, 'destroy']);
        Route::get('/toggleStatus/{user_id}', [UserController::class, 'toggleStatus']); // Changed from GET to PATCH
    });
});
