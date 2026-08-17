<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Student;

Route::get('/students', function () {
    return Student::all();
});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
