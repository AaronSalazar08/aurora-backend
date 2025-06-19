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
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\PedidoEstadoController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\ClienteConsultaController;
use App\Http\Controllers\Api\ProductoFiltroController;
use App\Http\Controllers\Api\ResenaController;
use App\Http\Controllers\Api\MetodoPagoController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(DireccionController::class)->group(function () {
    Route::get('/paises', 'getPaises');
    Route::get('/paises/provincias/{id_pais}', 'getProvincias');
    Route::get('/paises/provincias/cantones/{id_provincia}', 'getCantones');
    Route::get('/paises/provincias/cantones/distritos/{id_canton}', 'getDistritos');
    Route::get('/paises/provincias/cantones/distritos/barrios/{id_distrito}', 'getBarrios');
});


Route::get('/tiposcontacto', [TipoContacto::class, 'index']);


Route::get('/usuario', [UsuarioController::class, 'index']);
Route::post('/usuario', [UsuarioController::class, 'AgregueUnUsuario']);
Route::post('/personalenvios', [UsuarioController::class, 'agregarPersonalEnvios']);
Route::get('/listarpersonalenvios', [UsuarioController::class, 'listarPersonalEnvios']);
Route::post('/login', [LoginController::class, 'InicieUnaSesion']);


Route::post('/registrocompleto', [RegistroCompletoController::class, 'registrarTodo']);

Route::controller(RegistroCompletoController::class)->group(function () {
    Route::post('/registrar-usuario', 'registrarUsuario');
    Route::post('/registrar-direccion', 'registrarDireccion');
    Route::post('/registrar-telefono', 'registrarTelefono');
    Route::post('/registrar-correo', 'registrarCorreo');
});

Route::get('/listarproductos', [ProductoController::class, 'listarProductos']);
Route::get('/categoriaproductos', [ProductoController::class, 'listarCategorias']);
Route::post('/productos', [ProductoController::class, 'agregarProducto']);
Route::put('/productos/{codigo}', [ProductoController::class, 'actualizarProducto']);
Route::delete('/productos/{codigo}', [ProductoController::class, 'eliminarProducto']);
Route::get('/productos/{codigo}', [ProductoController::class, 'buscarProducto']);


Route::get('/listarpedidos', [PedidoController::class, 'listarPedidos']);
Route::controller(PedidoController::class)->group(function () {
    Route::get('/pedidos/pendientes', 'pendientes');
    Route::get('/pedidos/enproceso', 'enProceso');
    Route::get('/pedidos/enviados', 'enviados');
    Route::get('/pedidos/entregados', 'entregados');
    Route::get('/pedidos/cliente/{cedula}', 'pedidosPorCliente');

    // ESTA VA AL FINAL
    Route::get('/pedidos/{codigo}', 'buscarPedido');

    Route::post('/pedidos', 'agregarPedido');
    Route::put('/pedidos/simular-cambio', [PedidoEstadoController::class, 'simularCambioDeEstado']);

    Route::put('/pedidos/{codigo}', 'actualizarPedido');
    Route::delete('/pedidos/{codigo}', 'eliminarPedido');
});


Route::controller(ClienteController::class)->group(function () {
    Route::put('/clientes/{identificacion}', 'actualizarCliente');
    Route::delete('/clientes/{identificacion}', 'eliminarCliente');
});

Route::get('/estados-aprobacion', [PedidoEstadoController::class, 'listarEstadosAprobacion']);

Route::controller(PedidoEstadoController::class)->group(function () {
    Route::put('/pedidos/admin/estado', 'actualizarEstadoAdmin');
    Route::put('/pedidos/envios/estado', 'actualizarEstadoEnvios');
});

// Facturas
Route::controller(FacturaController::class)->group(function () {
    Route::get('/facturas', 'todas');
    Route::get('/facturas/cliente/{id}', 'porCliente');
    Route::get('/facturas/{id}', 'detalle');
    Route::post('/facturas/procesar', 'procesarFactura');
    Route::put('/facturas/{id}', 'actualizar');
    Route::delete('/facturas/{id}', 'eliminar');
});


// Consultas de clientes
Route::controller(ClienteConsultaController::class)->group(function () {
    Route::get('/clientes', 'todos');
    Route::get('/clientes/{cedula}', 'porIdentificacion');
    Route::get('/clientes/usuario/{id_usuario}', 'porUsuario');
});

// Productos por categoría
Route::controller(ProductoFiltroController::class)->group(function () {
    Route::get('/productos/ropa', 'ropa');
    Route::get('/productos/accesorios', 'accesorios');
    Route::get('/productos/moda', 'moda');
});

Route::get('/buscarResena/{id}', [ResenaController::class, 'buscarResena']);

Route::get('/tipospago', [MetodoPagoController::class, 'listarTipos']);

Route::controller(ResenaController::class)->group(function () {
    Route::get('/resenas', 'ListarResenas');
    Route::get('/resenas/{id}', 'verdetalleResena');
    Route::post('/resenas', 'agregarResena');
    Route::delete('/resenas/{id}', 'eliminarResena');
});
