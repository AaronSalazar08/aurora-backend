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
Route::post('/usuario', [App\Http\Controllers\Api\UsuarioController::class, 'AgregueUnUsuario']);
Route::post('/login', [App\Http\Controllers\Api\LoginController::class, 'InicieUnaSesion']);
//Route::post('/registro-completo', [App\Http\Controllers\Api\RegistroCompletoController::class, 'registrarTodo']);

Route::controller(App\Http\Controllers\Api\RegistroCompletoController::class)->group(function () {
    Route::post('/registro-completo', 'registrarTodo');
    Route::post('/registrar-usuario', 'registrarUsuario');
    Route::post('/registrar-direccion', 'registrarDireccion');
    Route::post('/registrar-telefono', 'registrarTelefono');
    Route::post('/registrar-correo', 'registrarCorreo');
});

