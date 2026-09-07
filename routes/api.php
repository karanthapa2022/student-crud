<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Addresses\AddressController;
use App\Http\Controllers\Api\Students\StudentController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\Parents\ParentController;
use App\Http\Controllers\Api\Subjects\SubjectController;
use App\Http\Controllers\Api\Marksheets\MarksheetController;
use App\Http\Controllers\Api\Parents\ParentAuthController;
use App\Http\Controllers\Api\DashboardController;


// =========================================================
// PUBLIC AUTHENTICATION
// =========================================================

Route::post('/register', [ApiAuthController::class, 'register']);

Route::post('/login', [ApiAuthController::class, 'login']);

Route::post('/parent/login', [ParentAuthController::class, 'login']);


// =========================================================
// AUTHENTICATED ROUTES
// =========================================================

Route::middleware('auth:sanctum')->group(function () {


        Route::middleware('role:admin')->group(function () {

    Route::get('/dashboard/statistics', [
        DashboardController::class,
        'statistics'
    ]);

});
    // =====================================================
    // COMMON AUTHENTICATED ROUTES
    // =====================================================

    Route::post('/logout', [ApiAuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::put('/profile', [
        ApiAuthController::class,
        'updateProfile'
    ]);

    Route::post('/profile/photo', [
        ApiAuthController::class,
        'updateProfilePhoto'
    ]);

    Route::post('/profile/document', [
        ApiAuthController::class,
        'updateDocument'
    ]);

    Route::delete('/profile/document', [
        ApiAuthController::class,
        'deleteDocument'
    ]);

    Route::post('/profile/change-password', [
        ApiAuthController::class,
        'changePassword'
    ]);


    // =====================================================
    // MARKSHEET ROUTES
    // =====================================================

    Route::apiResource(
        'marksheets',
        MarksheetController::class
    )->only([
        'index',
        'show',
        'store',
        'update',
        'destroy'
    ]);

    Route::post(
        '/marksheets/parent-search',
        [MarksheetController::class, 'parentSearch']
    );
    // =====================================================
// SUBJECT VIEW ROUTES
// =====================================================

Route::middleware('role:admin,teacher')->group(function () {

    Route::get(
        '/subjects',
        [SubjectController::class, 'index']
    );

    Route::get(
        '/subjects/{id}',
        [SubjectController::class, 'show']
    );

});

// =====================================================
// STUDENT VIEW ROUTES
// Admin + Teacher
// =====================================================

Route::middleware('role:admin,teacher')->group(function () {

    Route::get(
        '/students',
        [StudentController::class, 'index']
    );

    Route::get(
        '/students/{id}',
        [StudentController::class, 'show']
    );

});


    // =====================================================
    // ADMIN ROUTES
    // =====================================================

    Route::middleware('role:admin')->group(function () {


        // -------------------------------------------------
        // PARENTS
        // -------------------------------------------------

        Route::apiResource(
            'parents',
            ParentController::class
        );

        Route::put(
            '/parents/{parent}/children',
            [ParentController::class, 'updateChildren']
        );


        // -------------------------------------------------
        // ADDRESSES
        // -------------------------------------------------

        Route::apiResource(
            'addresses',
            AddressController::class
        );


        // -------------------------------------------------
// SUBJECT MANAGEMENT
// -------------------------------------------------

Route::post(
    '/subjects',
    [SubjectController::class, 'store']
);

Route::put(
    '/subjects/{id}',
    [SubjectController::class, 'update']
);

Route::delete(
    '/subjects/{id}',
    [SubjectController::class, 'destroy']
);


        // =====================================================
// STUDENT SPECIAL ROUTES
// These MUST come before apiResource()
// =====================================================

Route::post('/students/bulk-delete', [StudentController::class, 'bulkDelete']);

Route::put('/students/bulk-update', [StudentController::class, 'bulkUpdate']);

Route::get('/students/statistics', [StudentController::class, 'statistics']);

Route::get('/students/trash', [StudentController::class, 'trash']);

Route::post('/students/bulk-restore', [StudentController::class, 'bulkRestore']);

Route::post('/students/bulk-force-delete', [StudentController::class, 'bulkForceDelete']);

Route::post('/students/{id}/restore', [StudentController::class, 'restore']);

Route::delete('/students/{id}/force-delete', [StudentController::class, 'forceDelete']);


// =====================================================
// STUDENT CRUD
// =====================================================

Route::apiResource('students', StudentController::class)
    ->only([
        'store',
        'update',
        'destroy',
    ]);
    });


    // =====================================================
    // TEACHER ROUTES
    // =====================================================

    Route::middleware('role:teacher')->group(function () {

        // Teacher-specific routes
        // will be added here later.

    });


    // =====================================================
    // PARENT ROUTES
    // =====================================================

    Route::middleware('role:parent')->group(function () {

        // Parent-specific routes
        // will be added here later.

    });

});