<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Api\DireccionController;
use App\Http\Controllers\Api\TipoContacto;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegistroCompletoController;
use App\Http\Controllers\Api\ProductoController; 
use App\Http\Controllers\Api\PedidoController;

// ✅ RUTA PROTEGIDA CON SANCTUM
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ✅ RUTAS DE DIRECCIONES 
Route::controller(DireccionController::class)->group(function () {
    Route::get('/paises', 'getPaises');
    Route::get('/paises/provincias/{id_pais}', 'getProvincias');
    Route::get('/paises/provincias/cantones/{id_provincia}', 'getCantones');
    Route::get('/paises/provincias/cantones/distritos/{id_canton}', 'getDistritos');
    Route::get('/paises/provincias/cantones/distritos/barrios/{id_distrito}', 'getBarrios');
});

// ✅ TIPO DE CONTACTO
Route::get('/tiposcontacto', [TipoContacto::class, 'index']);

// ✅ USUARIO Y AUTENTICACIÓN
Route::get('/usuario', [UsuarioController::class, 'index']);
Route::post('/usuario', [UsuarioController::class, 'AgregueUnUsuario']);
Route::post('/login', [LoginController::class, 'InicieUnaSesion']);

// ✅ REGISTRO COMPLETO
Route::post('/registrocompleto', [RegistroCompletoController::class, 'registrarTodo']);

Route::controller(RegistroCompletoController::class)->group(function () {
    Route::post('/registrar-usuario', 'registrarUsuario');
    Route::post('/registrar-direccion', 'registrarDireccion');
    Route::post('/registrar-telefono', 'registrarTelefono');
    Route::post('/registrar-correo', 'registrarCorreo');
});

// ✅ PRODUCTO 
Route::post('/productos', [ProductoController::class, 'agregarProducto']);
Route::put('/productos/{codigo}', [ProductoController::class, 'actualizarProducto']);
Route::delete('/productos/{codigo}', [ProductoController::class, 'eliminarProducto']);
Route::get('/productos/{codigo}', [ProductoController::class, 'buscarProducto']);

Route::controller(PedidoController::class)->group(function () {
    Route::post('/pedidos', 'agregarPedido');
    Route::put('/pedidos/{codigo}', 'actualizarPedido');
    Route::delete('/pedidos/{codigo}', 'eliminarPedido');
    Route::get('/pedidos/{codigo}', 'buscarPedido');
});