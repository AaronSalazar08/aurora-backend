<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\DireccionController;
use App\Http\Controllers\Api\TipoContacto;
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
use App\Http\Controllers\Api\CarritoController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

// Login
Route::post('login', [LoginController::class, 'login']);

// Direcciones
Route::controller(DireccionController::class)->group(function () {
    Route::get('paises', 'getPaises');
    Route::get('paises/provincias/{id_pais}', 'getProvincias');
    Route::get('paises/provincias/cantones/{id_provincia}', 'getCantones');
    Route::get('paises/provincias/cantones/distritos/{id_canton}', 'getDistritos');
    Route::get('paises/provincias/cantones/distritos/barrios/{id_distrito}', 'getBarrios');
});

// Tipos de contacto
Route::get('tipos-contacto', [TipoContacto::class, 'index']);

// Registro completo
Route::post('registro-completo', [RegistroCompletoController::class, 'registrarTodo']);

Route::prefix('registro')->controller(RegistroCompletoController::class)->group(function () {
    Route::post('usuario', 'registrarUsuario');
    Route::post('direccion', 'registrarDireccion');
    Route::post('telefono', 'registrarTelefono');
    Route::post('correo', 'registrarCorreo');
});

// Productos (lectura pública)
Route::get('productos', [ProductoController::class, 'listarProductos']);
Route::get('productos/detalle/{codigo}', [ProductoController::class, 'detalleProducto']);
Route::get('productos/categorias', [ProductoController::class, 'listarCategorias']);

// Pedidos (lectura pública)
Route::get('pedidos', [PedidoController::class, 'listarPedidos']);
Route::get('pedidos/cliente/{cedula}', [PedidoController::class, 'pedidosPorCliente']);
Route::get('pedidos/pendientes', [PedidoController::class, 'pendientes']);
Route::get('pedidos/enproceso', [PedidoController::class, 'enProceso']);
Route::get('pedidos/enviados', [PedidoController::class, 'enviados']);
Route::get('pedidos/entregados', [PedidoController::class, 'entregados']);
Route::get('pedidos/{codigo}', [PedidoController::class, 'buscarPedido']);

// Facturas (lectura pública)
Route::get('facturas', [FacturaController::class, 'todas']);
Route::get('facturas/cliente/{id}', [FacturaController::class, 'porCliente']);
Route::get('facturas/{id}', [FacturaController::class, 'detalle']);

// Consultas de clientes
Route::get('clientes', [ClienteConsultaController::class, 'todos']);
Route::get('clientes/{cedula}', [ClienteConsultaController::class, 'porIdentificacion']);
Route::get('clientes/usuario/{id}', [ClienteConsultaController::class, 'porUsuario']);

// Filtros de productos
Route::get('productos/ropa', [ProductoFiltroController::class, 'ropa']);
Route::get('productos/accesorios', [ProductoFiltroController::class, 'accesorios']);
Route::get('productos/moda', [ProductoFiltroController::class, 'moda']);

// Reseñas y métodos de pago (públicos)
Route::get('buscar-resena/{id}', [ResenaController::class, 'buscarResena']);
Route::get('tipos-pago', [MetodoPagoController::class, 'listarTipos']);

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren token Sanctum)
|--------------------------------------------------------------------------
*/

// Perfil
Route::get('user', [LoginController::class, 'perfil']);

// Usuarios
Route::get('usuarios', [UsuarioController::class, 'index']);
Route::post('usuarios', [UsuarioController::class, 'agregarUsuario']);

// Personal de envíos
Route::get('usuarios/personal-envios', [UsuarioController::class, 'listarPersonalEnvios']);
Route::post('usuarios/personal-envios', [UsuarioController::class, 'agregarPersonalEnvios']);

// CRUD Productos
Route::post('productos', [ProductoController::class, 'agregarProducto']);
Route::put('productos/{codigo}', [ProductoController::class, 'actualizarProducto']);
Route::delete('productos/{codigo}', [ProductoController::class, 'eliminarProducto']);
Route::get('productos/{codigo}', [ProductoController::class, 'buscarProducto']);

// CRUD Pedidos
Route::post('/pedidos/auto', [PedidoController::class, 'agregarPedidoAuto']);
Route::put('pedidos/{codigo}', [PedidoController::class, 'actualizarPedido']);
Route::delete('pedidos/{codigo}', [PedidoController::class, 'eliminarPedido']);

// Clientes (update/delete)
Route::put('clientes/{identificacion}', [ClienteController::class, 'actualizarCliente']);
Route::delete('clientes/{identificacion}', [ClienteController::class, 'eliminarCliente']);
Route::get('clientes/usuario/{idUsuario}', [ClienteController::class, 'porUsuario']);


// Estados de pedido
Route::put('pedidos/admin/estado', [PedidoEstadoController::class, 'actualizarEstadoAdmin']);
Route::put('pedidos/envios/estado', [PedidoEstadoController::class, 'actualizarEstadoEnvios']);

// Facturas (write)
Route::post('facturas/procesar', [FacturaController::class, 'procesarFactura']);
Route::put('facturas/{id}', [FacturaController::class, 'actualizar']);
Route::delete('facturas/{id}', [FacturaController::class, 'eliminar']);

// Reseñas (CRUD)
Route::get('resenas', [ResenaController::class, 'ListarResenas']);
Route::get('resenas/{id}', [ResenaController::class, 'verdetalleResena']);
Route::post('resenas', [ResenaController::class, 'agregarResena']);
Route::delete('resenas/{id}', [ResenaController::class, 'eliminarResena']);

// Carrito
Route::prefix('carrito')->group(function () {
    Route::get('{codigo_pedido}', [CarritoController::class, 'index']);
    Route::post('agregar', [CarritoController::class, 'agregarProducto']);
    Route::put('{id}', [CarritoController::class, 'actualizarProducto']);
    Route::delete('{id}', [CarritoController::class, 'eliminarProducto']);
});

