<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\ProjectController;
use App\Http\Controllers\V1\TimesheetController;
use App\Http\Controllers\V1\PeopleController;
use App\Http\Controllers\V1\OccasionController;
use Illuminate\Http\Request;
Route::middleware('throttle:api')->group(function () {

// Public (no auth)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

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
    Route::middleware('auth:api')->get('/me', function (Request $request) {
    return response()->json([
        'auth_id' => auth()->id(),
        'guard_id' => auth('api')->id(),
        'user' => auth()->user()
    ]);
});
});

