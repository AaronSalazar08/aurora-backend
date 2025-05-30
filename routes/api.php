<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(App\Http\Controllers\Api\DireccionController::class)->group(function () {
    Route::get('/paises', 'getPaises');
    Route::get('/paises/provincias/{id_pais}', 'getProvincias');
    Route::get('/paises/provincias/cantones/{id_provincia}', 'getCantones');
    Route::get('/paises/provincias/cantones/distritos/{id_canton}', 'getDistritos');
    Route::get('/paises/provincias/cantones/distritos/barrios/{id_distrito}', 'getBarrios');
});


Route::get('/usuario', [App\Http\Controllers\Api\usuarioController::class, 'index']);
Route::post('/usuario', [App\Http\Controllers\Api\usuarioController::class, 'AgregueUnUsuario']);
Route::post('/login', [App\Http\Controllers\Api\LoginController::class, 'InicieUnaSesion']);
