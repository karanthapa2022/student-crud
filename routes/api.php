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
use App\Http\Controllers\Api\Teachers\TeacherController;
use App\Http\Controllers\Api\Teachers\TeacherStudentController;
use App\Http\Controllers\Api\Teachers\TeacherSubjectController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\NotificationController;

// =========================================================
// PUBLIC AUTHENTICATION
// =========================================================

Route::post('/register', [ApiAuthController::class, 'register'])
    ->middleware('throttle:10,1');

Route::post('/login', [ApiAuthController::class, 'login'])
    ->middleware('throttle:10,1');

Route::post('/parent/login', [ParentAuthController::class, 'login'])
    ->middleware('throttle:10,1');


// =========================================================
// AUTHENTICATED ROUTES
// =========================================================

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

Route::get('/notifications', [
    NotificationController::class,
    'index'
]);

Route::get('/notifications/unread-count', [
    NotificationController::class,
    'unreadCount'
]);
Route::patch('/notifications/{id}/read', [
    NotificationController::class,
    'markAsRead'
]);

Route::patch('/change-password',[
    \App\Http\Controllers\Api\UserController::class,
    'changePassword'
]);



Route::middleware('role:student')->group(function () {

        Route::post('/complaints', [
            ComplaintController::class,
            'store'
        ])->middleware('throttle:10,1');

        Route::get('/teachers-for-complaint', [
            \App\Http\Controllers\Api\Teachers\TeacherController::class,
            'forStudents'
        ]);

    });

    // Complaint detail - accessible by admin and assigned teacher
    Route::get('/complaints/{complaint}', [
        ComplaintController::class,
        'show'
    ])->middleware('role:admin,teacher');

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
    // ADMIN DASHBOARD
    // =====================================================

    Route::middleware('role:admin,teacher,student,parent')->group(function () {
        Route::get('/dashboard', [
            DashboardController::class,
            'index'
        ]);
    });

    Route::middleware('role:admin')->group(function () {

        Route::get('/dashboard/statistics', [
            DashboardController::class,
            'statistics'
        ]);

    });


    // =====================================================
    // SUBJECT VIEW ROUTES
    // Admin + Teacher
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
            '/students/{student}',
            [StudentController::class, 'show']
        )->whereNumber('student');

    });


    // =====================================================
    // MARKSHEET ROUTES
    // =====================================================

    Route::middleware('role:admin,teacher')->group(function () {
        Route::post('/marksheets/import', [MarksheetController::class, 'import']);
        Route::get('/marksheets/export', [MarksheetController::class, 'export']);
        Route::get('/marksheets/{marksheet}/export', [MarksheetController::class, 'export']);
    });

    // Parent-specific student marksheet
    Route::get(
        '/marksheets/parent/student/{student}',
        [MarksheetController::class, 'parentStudentMarksheet']
    );

    // Parent marksheet search
    Route::post(
        '/marksheets/parent-search',
        [MarksheetController::class, 'parentSearch']
    );

    // Admin / Teacher / Parent marksheet routes
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


    // =====================================================
    // ADMIN ROUTES
    // =====================================================

    Route::middleware('role:admin')->group(function () {

        Route::apiResource('users', UserController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);


        // -------------------------------------------------
        // TEACHERS
        // -------------------------------------------------

        Route::apiResource(
            'teachers',
            TeacherController::class
        );


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

        Route::put(
            '/parents/{parent}/change-password',
            [ParentController::class, 'changePassword']
        );

        Route::put('/teachers/{teacher}/subjects', [TeacherController::class, 'assignSubjects']);

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


        // =================================================
        // STUDENT SPECIAL ROUTES
        // These MUST come before apiResource()
        // =================================================

        Route::post(
            '/students/bulk-delete',
            [StudentController::class, 'bulkDelete']
        );

        Route::put(
            '/students/bulk-update',
            [StudentController::class, 'bulkUpdate']
        );

        Route::get(
            '/students/statistics',
            [StudentController::class, 'statistics']
        );

        Route::get(
            '/students/trash',
            [StudentController::class, 'trash']
        );

        Route::put(
            '/students/{student}/parent',
            [StudentController::class, 'assignParent']
        );

        Route::post(
            '/students/bulk-restore',
            [StudentController::class, 'bulkRestore']
        );

        Route::post(
            '/students/bulk-force-delete',
            [StudentController::class, 'bulkForceDelete']
        );

        Route::post(
            '/students/{id}/restore',
            [StudentController::class, 'restore']
        );

        Route::delete(
            '/students/{id}/force-delete',
            [StudentController::class,
            'forceDelete'
        ]);


        // =================================================
        // STUDENT CRUD
        // =================================================

        Route::apiResource(
            'students',
            StudentController::class
        )->only([
            'store',
            'update',
            'destroy',
        ]);
        Route::post('/students/{id}/reset-password', [StudentController::class, 'resetPassword']);

    });


    // =====================================================
    // TEACHER ROUTES
    // =====================================================

    Route::middleware('role:teacher')->group(function () {

        // -------------------------------------------------
        // TEACHER STUDENTS
        // -------------------------------------------------

        Route::get(
            '/teacher/students',
            [TeacherStudentController::class, 'index']
        );

        Route::get(
            '/teacher/students/{student}',
            [TeacherStudentController::class, 'show']
        );


        // -------------------------------------------------
        // TEACHER SUBJECTS
        // -------------------------------------------------

        Route::get(
            '/teacher/subjects',
            [TeacherSubjectController::class, 'index']
        );

        Route::get(
            '/teacher/subjects/{id}',
            [TeacherSubjectController::class, 'show']
        );

    });


    // =====================================================
    // PARENT ROUTES
    // =====================================================

    Route::middleware('role:parent')->group(function () {

        // -------------------------------------------------
        // PARENT PROFILE
        // -------------------------------------------------

        Route::get(
            '/parent/profile',
            [ParentAuthController::class, 'profile']
        );

        Route::put(
            '/parent/profile',
            [ParentAuthController::class, 'updateProfile']
        );


    });

});

