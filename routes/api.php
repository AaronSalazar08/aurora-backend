<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/pais', [App\Http\Controllers\Api\DireccionController::class, 'ObtenerPais']);
Route::get('/provincia?pais_id={id}', [App\Http\Controllers\Api\DireccionController::class, 'ObtenerProvincia']);
Route::get('/canton?provincia_id{id}', [App\Http\Controllers\Api\DireccionController::class, 'ObtenerCanton']);
Route::get('/distrito?canton_id{id}', [App\Http\Controllers\Api\DireccionController::class, 'ObtenerDistrito']);
Route::get('/barrio?distrito_id{id}', [App\Http\Controllers\Api\DireccionController::class, 'ObtenerBarrio']);

Route::post('/usuario', [App\Http\Controllers\Api\usuarioController::class, 'AgregueUnUsuario']);
Route::post('/login', [App\Http\Controllers\Api\LoginController::class, 'InicieUnaSesion']);
