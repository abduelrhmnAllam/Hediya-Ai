<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\ProjectController;
use App\Http\Controllers\V1\TimesheetController;
use App\Http\Controllers\V1\PeopleController;
use App\Http\Controllers\V1\OccasionController;
use App\Http\Controllers\V1\OpenaiController;
use App\Http\Controllers\V1\ProductController;
use Illuminate\Http\Request;
Route::middleware('throttle:api')->group(function () {



Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Public (no auth)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);


    Route::post('/search', [OpenaiController::class, 'search']);
    Route::post('/advanced-search', [OpenaiController::class, 'advancedSearch']);

Route::prefix('auth')->group(function () {
    Route::post('check-email', [AuthController::class, 'checkEmail']);      // مرحلة 1
    Route::post('verify-pin', [AuthController::class, 'verifyPin']);        // مرحلة 2
    Route::post('resend-pin', [AuthController::class, 'resendPin']);

    Route::post('complete-register', [AuthController::class, 'completeRegister']); // مرحلة 3
    Route::post('submit-password', [AuthController::class, 'submitPassword']); // login by password
    Route::post('login', [AuthController::class, 'login']); // يمكنك الاحتفاظ بالـ login الحالي
       Route::post('request-password-reset', [AuthController::class, 'requestPasswordReset']);
    Route::post('verify-reset-code', [AuthController::class, 'verifyResetCode']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);

    Route::get('google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('google/callback', [AuthController::class, 'handleGoogleCallback']);

});


// Protected (with Passport Token JWT)
    Route::middleware('auth:api')->group(function () {

       Route::post('logout', [AuthController::class, 'logout']);

        // CRUD for Users, Projects, Timesheets
        Route::apiResource('users', UserController::class);
        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('timesheets', TimesheetController::class);
            Route::apiResource('persons', PeopleController::class);
              Route::apiResource('occasions', OccasionController::class);
    });

});

