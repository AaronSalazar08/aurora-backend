<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/usuario', [App\Http\Controllers\Api\usuarioController::class, 'index']);
Route::post('/usuario', [App\Http\Controllers\Api\usuarioController::class, 'AgregueUnUsuario']);
Route::post('/login', [App\Http\Controllers\Api\LoginController::class, 'InicieUnaSesion']);
