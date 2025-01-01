<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/units', [UnitController::class, 'store']);
    Route::put('/units/{id}', [UnitController::class, 'update'])->name('units.update');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/images/bulk', [ImageController::class, 'storeBulk']);

});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/units/{unit}', [UnitController::class, 'showUnit']);
Route::get('/units', [UnitController::class, 'index']);
