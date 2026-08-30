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

    Route::post('/profile/document',[ApiAuthController::class,'updateDocument']);
    Route::delete('/profile/document',[ApiAuthController::class,'deleteDocument']);

    // Student API
    Route::post('/students/bulk-delete', [StudentController::class, 'bulkDelete']);
    Route::put('/students/bulk-update', [StudentController::class, 'bulkUpdate']);

    Route::get('/students/statistics', [StudentController::class, 'statistics']);

    Route::get('students/trash', [StudentController::class, 'trash']);
    Route::post('students/{id}/restore', [StudentController::class, 'restore']);
    Route::delete('students/{id}/force-delete', [StudentController::class, 'forceDelete']);

    Route::apiResource('students', StudentController::class);

    Route::post('/profile/change-password',[ApiAuthController::class,'changePassword']);
    
});