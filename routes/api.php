<?php

use Illuminate\Support\Facades\Route;

// Public Controllers
use App\Http\Controllers\Api\DireccionController;
use App\Http\Controllers\Api\TipoContacto;
use App\Http\Controllers\Api\RegistroCompletoController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ProductoFiltroController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ResenaController;
use App\Http\Controllers\Api\MetodoPagoController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ClienteConsultaController;

// Protected Controllers
use App\Http\Controllers\Api\PedidoEstadoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\ProductosCompradosController;

/*
|--------------------------------------------------------------------------
| Rutas públicas (no requieren autenticación)
|--------------------------------------------------------------------------
*/

Route::prefix('paises')->controller(DireccionController::class)->group(function () {
    Route::get('/', 'getPaises');
    Route::get('provincias/{id}', 'getProvincias');
    Route::get('provincias/{id}/cantones', 'getCantones');
    Route::get('provincias/{prov}/cantones/{can}/distritos', 'getDistritos');
    Route::get('distritos/{dist}/barrios', 'getBarrios');
});

Route::get('tipos-contacto', [TipoContacto::class, 'index']);
Route::post('registro-completo', [RegistroCompletoController::class, 'registrarTodo']);
Route::prefix('registro')->controller(RegistroCompletoController::class)->group(function () {
    Route::post('usuario', 'registrarUsuario');
    Route::post('direccion', 'registrarDireccion');
    Route::post('telefono', 'registrarTelefono');
    Route::post('correo', 'registrarCorreo');
});

Route::get('productos', [ProductoController::class, 'listarProductos']);
Route::get('productos/categorias', [ProductoController::class, 'listarCategorias']);
Route::get('productos/detalle/{codigo}', [ProductoController::class, 'detalleProducto']);

// Filtros de productos
Route::get('productos/ropa', [ProductoFiltroController::class, 'ropa']);
Route::get('productos/accesorios', [ProductoFiltroController::class, 'accesorios']);
Route::get('productos/moda', [ProductoFiltroController::class, 'moda']);

Route::get('pedidos', [PedidoController::class, 'listarPedidos']);
Route::get('usuarios/personal-envios', [UsuarioController::class, 'listarPersonalEnvios']);
Route::get('resenas', [ResenaController::class, 'ListarResenas']);
Route::get('clientes/usuario/{id}', [ClienteConsultaController::class, 'porUsuario']);
Route::get('pedidos/cliente/{cedula}', [ClienteConsultaController::class, 'porIdentificacion']);
Route::get('pedidos', [PedidoController::class, 'listarPedidos']);
Route::get('facturas/cliente/{id}', [FacturaController::class, 'porCliente']);
Route::get('tipos-pago', [MetodoPagoController::class, 'listarTipos']);

// Autenticación
Route::post('login', [LoginController::class, 'login']);
Route::post('usuarios', [UsuarioController::class, 'agregarUsuario']);


/*
|--------------------------------------------------------------------------
| Rutas privadas (requieren token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Perfil
    Route::get('user', [LoginController::class, 'perfil']);

    // Usuarios
    Route::get('usuarios', [UsuarioController::class, 'index']);
    Route::post('usuarios/personal-envios', [UsuarioController::class, 'agregarPersonalEnvios']);
    Route::delete('usuarios/personal-envios/{id}', [UsuarioController::class, 'eliminarPersonalEnvios']);

    // Productos
    Route::prefix('productos')->group(function () {
        Route::put('reducir-stock', [ProductoController::class, 'reducir_stock']);
        Route::post('/', [ProductoController::class, 'agregarProducto']);
        Route::put('{codigo}', [ProductoController::class, 'actualizarProducto']);
        Route::delete('{codigo}', [ProductoController::class, 'eliminarProducto']);
        Route::get('{codigo}', [ProductoController::class, 'buscarProducto']);
    });

    // Pedidos
    Route::prefix('pedidos')->group(function () {
        Route::post('auto', [PedidoController::class, 'agregarPedidoAuto']);
        Route::post('limpiar/{codigoPedido}', [PedidoController::class, 'limpiarCarrito']);
        Route::get('mis-pedidos', [PedidoController::class, 'misPedidos']);
        Route::put('{codigo}', [PedidoController::class, 'actualizarPedido']);
        Route::delete('{codigo}', [PedidoController::class, 'eliminarPedido']);
        Route::put('{codigo}/metodo-pago', [PedidoController::class, 'actualizarMetodoPago']);
    });

    // Estados de pedido
    Route::put('pedidos/admin/estado', [PedidoEstadoController::class, 'actualizarEstadoAdmin']);
    Route::put('pedidos/envios/estado', [PedidoEstadoController::class, 'actualizarEstadoEnvios']);
    Route::get('estados-pedido', [PedidoEstadoController::class, 'listarEstadosAprobacion']);

    // Carrito
    Route::prefix('carrito')->controller(CarritoController::class)->group(function () {
        Route::get('{codigo_pedido}', 'index');
        Route::post('agregar', 'agregarProducto');
        Route::put('{id}', 'actualizarProducto');
        Route::delete('{id}', 'eliminarProducto');
    });

    // Facturas
    Route::prefix('facturas')->controller(FacturaController::class)->group(function () {
        Route::get('/', 'listarFacturas');
        Route::get('detalle/{id}', 'detalle');
        Route::post('procesar/{codigo}', 'procesarFactura');
        Route::put('{id}', 'actualizar');
        Route::delete('{id}', 'eliminar');
        Route::get('mis-facturas', 'misFacturas');
    });

    // Reseñas
    Route::get('resenas/{id}', [ResenaController::class, 'verdetalleResena']);
    Route::post('resenas', [ResenaController::class, 'agregarResena']);
    Route::delete('resenas/{id}', [ResenaController::class, 'eliminarResena']);
    Route::get('pedidos-para-resena', [PedidoController::class, 'pedidosParaResena']);

    // Clientes
    Route::prefix('clientes')->group(function () {
        Route::get('usuario/{id}', [ClienteController::class, 'porUsuario']);
        Route::put('{identificacion}', [ClienteController::class, 'actualizarCliente']);
        Route::delete('{identificacion}', [ClienteController::class, 'eliminarCliente']);
    });

    // Productos Comprados
    Route::post('productos-comprados', [ProductosCompradosController::class, 'agregasProductosComprados']);
});
