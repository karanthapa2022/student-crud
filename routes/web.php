<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;


// Home page
Route::get('/', function () {
    return view('welcome');
});



// Show bulk edit page for selected students
Route::post('/students/bulk-edit', [StudentController::class, 'bulkEdit'])
    ->name('students.bulkEdit');

// Update selected students
Route::put('/students/bulk-update', [StudentController::class, 'bulkUpdate'])
    ->name('students.bulkUpdate');

    Route::delete('/students/bulk-delete', [StudentController::class, 'bulkDelete'])
    ->name('students.bulkDelete');

    Route::get('login',[AuthController::class,'showLoginForm'])
    ->name('login'); //gives the route a name.


    Route::post('/login',[AuthController::class,'login']);

    Route:: get('register',[AuthController::class,'showRegisterForm'])
    ->name('register');
    Route::post('/register', [AuthController::class, 'register'])
    ->name('register.store');

    Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

    Route::resource('students', StudentController::class)
    ->middleware('auth');  // Apply auth middleware to all student routes
