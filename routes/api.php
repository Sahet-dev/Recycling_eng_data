<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {



    return $request->user();

});
Route::post('/login', [AuthController::class, 'login']);
Route::post('/units', [UnitController::class, 'store']);
Route::get('/units/{unit}', [UnitController::class, 'showUnit']);
Route::get('/units', [UnitController::class, 'index']);
Route::put('/units/{id}', [UnitController::class, 'update'])->name('units.update');

