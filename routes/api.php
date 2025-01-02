<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/units/{id}', [UnitController::class, 'update'])->name('units.update');
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/quiz', [UnitController::class, 'storeQuiz']);
    Route::post('/images/bulk', [ImageController::class, 'storeBulk']);
    Route::post('/units', [UnitController::class, 'store']);
    Route::put('/quizzes/{unitId}', [UnitController::class, 'updateQuizByUnitId']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/units/{unit}', [UnitController::class, 'showUnit']);
Route::get('/units', [UnitController::class, 'index']);
Route::get('/quizzes/{unitId}', [UnitController::class, 'getQuizByUnitId']);



