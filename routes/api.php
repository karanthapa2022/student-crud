<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ApiAuthController;

// Public authentication routes
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [ApiAuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::put('/profile',[ApiAuthController::class,'updateProfile']);
    Route::post('/profile/photo',[ApiAuthController::class,'updateProfilePhoto']);

    // Student API
    Route::post('/students/bulk-delete', [StudentController::class, 'bulkDelete']);
    Route::put('/students/bulk-update', [StudentController::class, 'bulkUpdate']);

    Route::apiResource('students', StudentController::class);
});