<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;



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



Route::resource('students', StudentController::class);